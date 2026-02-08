<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

class TenantBackupService
{
    protected string $backupRoot;

    public function __construct()
    {
        $this->backupRoot = storage_path('app/tenant-backups');

        if (!File::exists($this->backupRoot)) {
            File::makeDirectory($this->backupRoot, 0755, true);
        }
    }

    public function list(int $tenantId): Collection
    {
        $path = $this->tenantPath($tenantId);
        if (!File::isDirectory($path)) {
            return collect();
        }

        $timezone = config('app.timezone') ?: date_default_timezone_get();

        return collect(File::files($path))
            ->filter(fn($file) => $file->getExtension() === 'sql')
            ->map(fn($file) => [
                'filename' => $file->getFilename(),
                'size_bytes' => $file->getSize(),
                'size' => $this->formatSize($file->getSize()),
                'created_at' => Carbon::createFromTimestamp($file->getMTime(), $timezone),
                'path' => $file->getPathname(),
            ])
            ->sortByDesc('created_at')
            ->values();
    }

    public function create(int $tenantId): array
    {
        $this->ensureTenantPath($tenantId);

        $timestamp = now()->format('Y-m-d_H-i-s');
        $filename = "tenant_{$tenantId}_backup_{$timestamp}.sql";
        $filepath = $this->tenantPath($tenantId) . '/' . $filename;

        $header = "-- TENANT_BACKUP tenant_id={$tenantId} created_at=" . now()->toDateTimeString() . "\n";
        $header .= "SET FOREIGN_KEY_CHECKS=0;\n";
        File::put($filepath, $header);

        $tables = $this->getTenantTables();
        $dbName = config('database.connections.mysql.database');

        foreach ($tables as $table) {
            $where = "tenant_id = {$tenantId}";
            $command = sprintf(
                'mysqldump --user=%s --password=%s --host=%s --port=%s --skip-comments --skip-add-locks --skip-lock-tables --no-create-db --no-create-info --skip-triggers --single-transaction --where=%s %s %s >> %s',
                escapeshellarg(config('database.connections.mysql.username')),
                escapeshellarg(config('database.connections.mysql.password')),
                escapeshellarg(config('database.connections.mysql.host')),
                escapeshellarg(config('database.connections.mysql.port', 3306)),
                escapeshellarg($where),
                escapeshellarg($dbName),
                escapeshellarg($table),
                escapeshellarg($filepath)
            );

            $result = Process::run($command);
            if ($result->failed()) {
                return [
                    'success' => false,
                    'message' => 'Tenant backup failed: ' . $result->errorOutput(),
                ];
            }
        }

        $this->appendUserPivotData($tenantId, $filepath);

        File::append($filepath, "\nSET FOREIGN_KEY_CHECKS=1;\n");

        if (!File::exists($filepath)) {
            return [
                'success' => false,
                'message' => 'Tenant backup failed to create the SQL file.',
            ];
        }

        return [
            'success' => true,
            'filename' => $filename,
            'size' => $this->formatSize(File::size($filepath)),
            'path' => $filepath,
        ];
    }

    public function getPath(int $tenantId, string $filename): ?string
    {
        $safeName = basename($filename);
        $path = $this->tenantPath($tenantId) . '/' . $safeName;

        if (File::exists($path) && str_ends_with($safeName, '.sql')) {
            return $path;
        }

        return null;
    }

    public function delete(int $tenantId, string $filename): bool
    {
        $path = $this->getPath($tenantId, $filename);
        if (!$path) {
            return false;
        }

        return File::delete($path);
    }

    public function restore(int $tenantId, string $filename): array
    {
        $path = $this->getPath($tenantId, $filename);

        if (!$path) {
            return [
                'success' => false,
                'message' => 'Backup file not found.',
            ];
        }

        return $this->restoreFromFile($tenantId, $path, $filename);
    }

    public function restoreFromUpload(int $tenantId, UploadedFile $file): array
    {
        if ($file->getClientOriginalExtension() !== 'sql') {
            return [
                'success' => false,
                'message' => 'Invalid file type. Only .sql files are allowed.',
            ];
        }

        $this->ensureTenantPath($tenantId);
        $tempName = 'upload_' . time() . '_' . $file->getClientOriginalName();
        $tempPath = $this->tenantPath($tenantId) . '/' . $tempName;
        $file->move($this->tenantPath($tenantId), $tempName);

        if (!File::exists($tempPath)) {
            return [
                'success' => false,
                'message' => 'Failed to upload backup file.',
            ];
        }

        $result = $this->restoreFromFile($tenantId, $tempPath, $file->getClientOriginalName());
        File::delete($tempPath);

        return $result;
    }

    protected function restoreFromFile(int $tenantId, string $filepath, string $filename): array
    {
        if (!$this->matchesTenant($tenantId, $filepath)) {
            return [
                'success' => false,
                'message' => 'This backup file does not match the selected company.',
            ];
        }

        $safety = $this->create($tenantId);
        $safetyNote = $safety['success']
            ? "Safety backup created: {$safety['filename']}."
            : 'Safety backup could not be created.';

        $tables = $this->getTenantTables();
        $existingUserIds = DB::table('users')
            ->where('tenant_id', $tenantId)
            ->pluck('id')
            ->all();

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        if (!empty($existingUserIds)) {
            if (DB::getSchemaBuilder()->hasTable('model_has_roles')) {
                DB::table('model_has_roles')
                    ->where('model_type', 'App\\Models\\User')
                    ->whereIn('model_id', $existingUserIds)
                    ->delete();
            }
            if (DB::getSchemaBuilder()->hasTable('model_has_permissions')) {
                DB::table('model_has_permissions')
                    ->where('model_type', 'App\\Models\\User')
                    ->whereIn('model_id', $existingUserIds)
                    ->delete();
            }
        }

        foreach ($tables as $table) {
            DB::table($table)->where('tenant_id', $tenantId)->delete();
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $command = sprintf(
            'mysql --user=%s --password=%s --host=%s --port=%s %s < %s',
            escapeshellarg(config('database.connections.mysql.username')),
            escapeshellarg(config('database.connections.mysql.password')),
            escapeshellarg(config('database.connections.mysql.host')),
            escapeshellarg(config('database.connections.mysql.port', 3306)),
            escapeshellarg(config('database.connections.mysql.database')),
            escapeshellarg($filepath)
        );

        $result = Process::run($command);

        if ($result->failed()) {
            return [
                'success' => false,
                'message' => 'Restore failed: ' . $result->errorOutput() . ' ' . $safetyNote,
            ];
        }

        return [
            'success' => true,
            'message' => 'Tenant data restored successfully. ' . $safetyNote,
            'filename' => $filename,
        ];
    }

    protected function appendUserPivotData(int $tenantId, string $filepath): void
    {
        $userIds = DB::table('users')
            ->where('tenant_id', $tenantId)
            ->pluck('id')
            ->all();

        if (empty($userIds)) {
            return;
        }

        $chunks = array_chunk($userIds, 500);
        $dbName = config('database.connections.mysql.database');

        $pivotTables = [];
        if (DB::getSchemaBuilder()->hasTable('model_has_roles')) {
            $pivotTables[] = 'model_has_roles';
        }
        if (DB::getSchemaBuilder()->hasTable('model_has_permissions')) {
            $pivotTables[] = 'model_has_permissions';
        }

        if (empty($pivotTables)) {
            return;
        }

        foreach ($pivotTables as $table) {
            foreach ($chunks as $chunk) {
                $ids = implode(',', array_map('intval', $chunk));
                $where = "model_type='App\\\\Models\\\\User' AND model_id IN ({$ids})";
                $command = sprintf(
                    'mysqldump --user=%s --password=%s --host=%s --port=%s --skip-comments --skip-add-locks --skip-lock-tables --no-create-db --no-create-info --skip-triggers --single-transaction --where=%s %s %s >> %s',
                    escapeshellarg(config('database.connections.mysql.username')),
                    escapeshellarg(config('database.connections.mysql.password')),
                    escapeshellarg(config('database.connections.mysql.host')),
                    escapeshellarg(config('database.connections.mysql.port', 3306)),
                    escapeshellarg($where),
                    escapeshellarg($dbName),
                    escapeshellarg($table),
                    escapeshellarg($filepath)
                );

                Process::run($command);
            }
        }
    }

    protected function matchesTenant(int $tenantId, string $filepath): bool
    {
        if (!File::exists($filepath)) {
            return false;
        }

        $handle = fopen($filepath, 'r');
        $header = $handle ? fread($handle, 2048) : '';
        if ($handle) {
            fclose($handle);
        }

        if (!$header) {
            return false;
        }

        if (preg_match('/tenant_id=(\d+)/', $header, $matches)) {
            return (int) $matches[1] === (int) $tenantId;
        }

        return false;
    }

    protected function getTenantTables(): array
    {
        $database = DB::getDatabaseName();
        $tables = DB::table('information_schema.columns')
            ->selectRaw('TABLE_NAME as table_name')
            ->where('TABLE_SCHEMA', $database)
            ->where('COLUMN_NAME', 'tenant_id')
            ->groupBy('TABLE_NAME')
            ->pluck('table_name')
            ->toArray();

        $tables = array_values(array_diff($tables, ['tenants']));
        sort($tables);

        return $tables;
    }

    protected function tenantPath(int $tenantId): string
    {
        return $this->backupRoot . '/tenant_' . $tenantId;
    }

    protected function ensureTenantPath(int $tenantId): void
    {
        $path = $this->tenantPath($tenantId);
        if (!File::exists($path)) {
            File::makeDirectory($path, 0755, true);
        }
    }

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
