<?php

namespace App\Console\Commands;

use App\Services\BackupService;
use Illuminate\Console\Command;

class BackupCleanup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:cleanup
                            {--days=30 : Delete backups older than this many days}
                            {--keep=20 : Keep maximum this many backups}
                            {--force : Skip confirmation prompt}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Cleanup old backup files to free up disk space';

    /**
     * Execute the console command.
     */
    public function handle(BackupService $backupService)
    {
        $this->info('Starting backup cleanup...');
        $this->newLine();

        $stats = $backupService->getStats();

        if ($stats['total_backups'] === 0) {
            $this->info('No backups found to cleanup.');
            return self::SUCCESS;
        }

        // Display current statistics
        $this->table(['Metric', 'Value'], [
            ['Total Backups', $stats['total_backups']],
            ['Database Backups', $stats['database_backups']],
            ['Full Backups', $stats['full_backups']],
            ['Total Size', $stats['total_size']],
        ]);

        $this->newLine();

        // Ask for confirmation
        if (!$this->option('force') && !$this->confirm('Do you want to proceed with cleanup?', true)) {
            $this->info('Cleanup cancelled.');
            return self::SUCCESS;
        }

        // Perform cleanup
        $backupService->cleanup();

        $newStats = $backupService->getStats();

        $this->newLine();
        $this->info('✓ Cleanup completed!');
        $this->newLine();

        $this->table(['Metric', 'Before', 'After'], [
            ['Total Backups', $stats['total_backups'], $newStats['total_backups']],
            ['Total Size', $stats['total_size'], $newStats['total_size']],
        ]);

        $freedSpace = $stats['total_size_bytes'] - $newStats['total_size_bytes'];
        if ($freedSpace > 0) {
            $this->info("Freed space: " . $this->formatBytes($freedSpace));
        }

        return self::SUCCESS;
    }

    /**
     * Format bytes to human readable size
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
