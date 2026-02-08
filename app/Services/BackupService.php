<?php
// app/Services/BackupService.php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use ZipArchive;

class BackupService
{
    protected string $backupPath;
    protected string $storagePath;
    protected array $config;

    public function __construct()
    {
        $this->config = [
            'path' => storage_path('app/backups'),
            'keep_days' => env('BACKUP_KEEP_DAYS', 30),
            'max_backups' => env('BACKUP_MAX_COUNT', 20),
            'file_directories' => [
                'products',
                'invoices',
                'documents',
                'imports',
                'exports',
                'attachments',
            ],
            'exclude_patterns' => [
                '.gitignore',
                '.gitkeep',
                '*.tmp',
                'Thumbs.db',
                '.DS_Store',
                '*.log',
            ],
        ];

        $this->backupPath = $this->config['path'];
        $this->storagePath = storage_path('app/public');

        if (!File::exists($this->backupPath)) {
            File::makeDirectory($this->backupPath, 0755, true);
        }
    }

    /**
     * Create a database-only backup
     */
    public function create(): array
    {
        $filename = 'db_backup_' . date('Y-m-d_H-i-s') . '.sql';
        $filepath = $this->backupPath . '/' . $filename;

        $command = sprintf(
            'mysqldump --user=%s --password=%s --host=%s --port=%s --single-transaction --routines --triggers %s > %s',
            escapeshellarg(config('database.connections.mysql.username')),
            escapeshellarg(config('database.connections.mysql.password')),
            escapeshellarg(config('database.connections.mysql.host')),
            escapeshellarg(config('database.connections.mysql.port', 3306)),
            escapeshellarg(config('database.connections.mysql.database')),
            escapeshellarg($filepath)
        );

        $result = Process::run($command);

        if ($result->failed() || !File::exists($filepath)) {
            return [
                'success' => false,
                'message' => 'Backup failed: ' . $result->errorOutput(),
            ];
        }

        // Cleanup old backups
        $this->cleanup();

        return [
            'success' => true,
            'filename' => $filename,
            'size' => $this->formatSize(File::size($filepath)),
            'path' => $filepath,
            'type' => 'database',
        ];
    }

    /**
     * Create a full backup (database + files) as ZIP
     */
    public function createFull(): array
    {
        $timestamp = date('Y-m-d_H-i-s');
        $zipFilename = 'full_backup_' . $timestamp . '.zip';
        $zipPath = $this->backupPath . '/' . $zipFilename;
        $tempSqlFile = $this->backupPath . '/temp_' . $timestamp . '.sql';

        // Step 1: Create database dump
        $dbCommand = sprintf(
            'mysqldump --user=%s --password=%s --host=%s --port=%s --single-transaction --routines --triggers %s > %s',
            escapeshellarg(config('database.connections.mysql.username')),
            escapeshellarg(config('database.connections.mysql.password')),
            escapeshellarg(config('database.connections.mysql.host')),
            escapeshellarg(config('database.connections.mysql.port', 3306)),
            escapeshellarg(config('database.connections.mysql.database')),
            escapeshellarg($tempSqlFile)
        );

        $result = Process::run($dbCommand);

        if ($result->failed() || !File::exists($tempSqlFile)) {
            return [
                'success' => false,
                'message' => 'Database backup failed: ' . $result->errorOutput(),
            ];
        }

        // Step 2: Create ZIP archive
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            File::delete($tempSqlFile);
            return [
                'success' => false,
                'message' => 'Failed to create ZIP archive.',
            ];
        }

        // Add database dump to ZIP
        $zip->addFile($tempSqlFile, 'database.sql');

        // Add files from storage
        $directories = $this->config['file_directories'];
        $excludePatterns = $this->config['exclude_patterns'];
        $filesAdded = 0;

        foreach ($directories as $dir) {
            $dirPath = $this->storagePath . '/' . $dir;
            if (File::isDirectory($dirPath)) {
                $filesAdded += $this->addDirectoryToZip($zip, $dirPath, 'files/' . $dir, $excludePatterns);
            }
        }

        $zip->close();

        // Clean up temp SQL file
        File::delete($tempSqlFile);

        if (!File::exists($zipPath)) {
            return [
                'success' => false,
                'message' => 'Failed to create backup ZIP file.',
            ];
        }

        // Cleanup old backups
        $this->cleanup();

        return [
            'success' => true,
            'filename' => $zipFilename,
            'size' => $this->formatSize(File::size($zipPath)),
            'path' => $zipPath,
            'type' => 'full',
            'files_count' => $filesAdded,
        ];
    }

    /**
     * Add directory contents to ZIP archive
     */
    protected function addDirectoryToZip(ZipArchive $zip, string $dirPath, string $zipDir, array $excludePatterns = []): int
    {
        $count = 0;
        $files = File::allFiles($dirPath);

        foreach ($files as $file) {
            $filename = $file->getFilename();

            // Check if file should be excluded
            $exclude = false;
            foreach ($excludePatterns as $pattern) {
                if (fnmatch($pattern, $filename)) {
                    $exclude = true;
                    break;
                }
            }

            if (!$exclude) {
                $relativePath = $zipDir . '/' . $file->getRelativePathname();
                $zip->addFile($file->getPathname(), $relativePath);
                $count++;
            }
        }

        return $count;
    }

    /**
     * List all backups
     */
    public function list(): Collection
    {
        $files = File::files($this->backupPath);

        return collect($files)
            ->filter(fn($file) => in_array($file->getExtension(), ['sql', 'zip']))
            ->map(fn($file) => [
                'filename' => $file->getFilename(),
                'size' => $this->formatSize($file->getSize()),
                'size_bytes' => $file->getSize(),
                'created_at' => Carbon::createFromTimestamp($file->getMTime()),
                'path' => $file->getPathname(),
                'type' => $file->getExtension() === 'zip' ? 'full' : 'database',
            ])
            ->sortByDesc('created_at')
            ->values();
    }

    /**
     * Get backup file path
     */
    public function getPath(string $filename): ?string
    {
        $filepath = $this->backupPath . '/' . basename($filename);

        if (File::exists($filepath) && (str_ends_with($filename, '.sql') || str_ends_with($filename, '.zip'))) {
            return $filepath;
        }

        return null;
    }

    /**
     * Delete a backup
     */
    public function delete(string $filename): bool
    {
        $filepath = $this->getPath($filename);

        if ($filepath) {
            return File::delete($filepath);
        }

        return false;
    }

    /**
     * Restore database from SQL or ZIP backup
     */
    public function restore(string $filename): array
    {
        $filepath = $this->getPath($filename);

        if (!$filepath) {
            return [
                'success' => false,
                'message' => 'Backup file not found.',
            ];
        }

        return $this->restoreFromFile($filepath, $filename);
    }

    /**
     * Restore database from uploaded file
     */
    public function restoreFromUpload($uploadedFile): array
    {
        $filename = $uploadedFile->getClientOriginalName();
        $extension = $uploadedFile->getClientOriginalExtension();

        if (!in_array($extension, ['sql', 'zip'])) {
            return [
                'success' => false,
                'message' => 'Invalid file type. Only .sql and .zip files are allowed.',
            ];
        }

        // Store uploaded file temporarily
        $tempPath = $this->backupPath . '/upload_' . time() . '.' . $extension;
        $uploadedFile->move($this->backupPath, basename($tempPath));

        if (!File::exists($tempPath)) {
            return [
                'success' => false,
                'message' => 'Failed to upload backup file.',
            ];
        }

        $result = $this->restoreFromFile($tempPath, $filename);

        // Clean up uploaded file after restore
        File::delete($tempPath);

        return $result;
    }

    /**
     * Internal method to restore from a file path
     */
    protected function restoreFromFile(string $filepath, string $filename): array
    {
        // If it's a ZIP file, extract database.sql first
        $sqlFile = $filepath;
        $tempDir = null;

        if (str_ends_with($filepath, '.zip')) {
            $tempDir = $this->backupPath . '/temp_restore_' . time();
            File::makeDirectory($tempDir, 0755, true);

            $zip = new ZipArchive();
            if ($zip->open($filepath) !== true) {
                return [
                    'success' => false,
                    'message' => 'Failed to open ZIP archive.',
                ];
            }

            $zip->extractTo($tempDir);
            $zip->close();

            $sqlFile = $tempDir . '/database.sql';

            if (!File::exists($sqlFile)) {
                File::deleteDirectory($tempDir);
                return [
                    'success' => false,
                    'message' => 'No database.sql found in backup archive.',
                ];
            }
        }

        $command = sprintf(
            'mysql --user=%s --password=%s --host=%s --port=%s %s < %s',
            escapeshellarg(config('database.connections.mysql.username')),
            escapeshellarg(config('database.connections.mysql.password')),
            escapeshellarg(config('database.connections.mysql.host')),
            escapeshellarg(config('database.connections.mysql.port', 3306)),
            escapeshellarg(config('database.connections.mysql.database')),
            escapeshellarg($sqlFile)
        );

        $result = Process::run($command);

        // Clean up temp directory if used
        if ($tempDir) {
            File::deleteDirectory($tempDir);
        }

        if ($result->failed()) {
            return [
                'success' => false,
                'message' => 'Restore failed: ' . $result->errorOutput(),
            ];
        }

        return [
            'success' => true,
            'message' => 'Database restored successfully from ' . $filename,
            'filename' => $filename,
        ];
    }

    /**
     * Restore files from ZIP backup
     */
    public function restoreFiles(string $filename): array
    {
        $filepath = $this->getPath($filename);

        if (!$filepath || !str_ends_with($filename, '.zip')) {
            return [
                'success' => false,
                'message' => 'Invalid backup file. Only ZIP files contain stored files.',
            ];
        }

        $zip = new ZipArchive();
        if ($zip->open($filepath) !== true) {
            return [
                'success' => false,
                'message' => 'Failed to open ZIP archive.',
            ];
        }

        $filesRestored = 0;

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entryName = $zip->getNameIndex($i);

            // Only extract files from 'files/' directory
            if (str_starts_with($entryName, 'files/') && !str_ends_with($entryName, '/')) {
                $relativePath = substr($entryName, 6); // Remove 'files/' prefix
                $targetPath = $this->storagePath . '/' . $relativePath;

                // Ensure directory exists
                $targetDir = dirname($targetPath);
                if (!File::isDirectory($targetDir)) {
                    File::makeDirectory($targetDir, 0755, true);
                }

                // Extract file
                $content = $zip->getFromIndex($i);
                if ($content !== false) {
                    File::put($targetPath, $content);
                    $filesRestored++;
                }
            }
        }

        $zip->close();

        return [
            'success' => true,
            'message' => "Restored {$filesRestored} files successfully.",
            'files_restored' => $filesRestored,
        ];
    }

    /**
     * Get backup statistics
     */
    public function getStats(): array
    {
        $backups = $this->list();
        $totalSize = $backups->sum('size_bytes');

        return [
            'total_backups' => $backups->count(),
            'database_backups' => $backups->where('type', 'database')->count(),
            'full_backups' => $backups->where('type', 'full')->count(),
            'total_size' => $this->formatSize($totalSize),
            'total_size_bytes' => $totalSize,
            'latest_backup' => $backups->first(),
            'oldest_backup' => $backups->last(),
        ];
    }

    /**
     * Cleanup old backups
     */
    public function cleanup(): void
    {
        $keepDays = $this->config['keep_days'];
        $maxBackups = $this->config['max_backups'];

        $backups = $this->list();

        // Delete old backups
        $backups->each(function ($backup) use ($keepDays) {
            if ($backup['created_at']->diffInDays(now()) > $keepDays) {
                File::delete($backup['path']);
            }
        });

        // Keep only max backups
        $backups = $this->list();
        if ($backups->count() > $maxBackups) {
            $backups->slice($maxBackups)->each(fn($b) => File::delete($b['path']));
        }
    }

    /**
     * Format bytes to human readable size
     */
    protected function formatSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}
