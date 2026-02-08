<?php

namespace App\Console\Commands;

use App\Services\BackupService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class RestoreBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:restore
                            {--file= : Specific backup file to restore (optional - will prompt if not provided)}
                            {--files : Also restore files from ZIP backup}
                            {--force : Skip confirmation prompt}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Restore database from a backup file';

    /**
     * Execute the console command.
     */
    public function handle(BackupService $backupService)
    {
        $this->warn('⚠️  BACKUP RESTORE - This will overwrite your current database!');
        $this->newLine();

        // Get list of available backups
        $backups = $backupService->list();

        if ($backups->isEmpty()) {
            $this->error('No backup files found.');
            return self::FAILURE;
        }

        // Get backup file
        $backupFile = $this->option('file');

        if (!$backupFile) {
            $choices = $backups->map(function ($backup) {
                return $backup['filename'] . ' (' . $backup['size'] . ') - ' . $backup['created_at']->format('Y-m-d H:i:s');
            })->toArray();

            $selected = $this->choice(
                'Select backup file to restore:',
                $choices,
                0
            );

            // Extract filename from choice
            $backupFile = explode(' ', $selected)[0];
        }

        // Verify file exists
        if (!$backupService->getPath($backupFile)) {
            $this->error("Backup file not found: {$backupFile}");
            return self::FAILURE;
        }

        // Display confirmation
        $this->newLine();
        $this->table(['Property', 'Value'], [
            ['File', $backupFile],
            ['Action', 'Restore Database'],
        ]);

        $this->newLine();

        // Ask for confirmation
        if (!$this->option('force') && !$this->confirm('Are you absolutely sure? This action cannot be undone!', false)) {
            $this->info('Restore cancelled.');
            return self::SUCCESS;
        }

        // Perform restore
        $this->info('Restoring database...');
        $result = $backupService->restore($backupFile);

        if ($result['success']) {
            $this->info("✓ Database restored successfully!");

            // Optionally restore files from ZIP
            if ($this->option('files') && str_ends_with($backupFile, '.zip')) {
                $this->newLine();
                $this->info('Restoring files from ZIP...');

                $filesResult = $backupService->restoreFiles($backupFile);

                if ($filesResult['success']) {
                    $this->info("✓ {$filesResult['message']}");
                } else {
                    $this->warn("⚠️  File restoration failed: {$filesResult['message']}");
                }
            }

            return self::SUCCESS;
        }

        $this->error("✗ Restore failed: {$result['message']}");
        return self::FAILURE;
    }
}
