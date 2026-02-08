<?php

namespace App\Console\Commands;

use App\Services\BackupService;
use Illuminate\Console\Command;

class BackupStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:status';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Display backup statistics and recent backups';

    /**
     * Execute the console command.
     */
    public function handle(BackupService $backupService)
    {
        $stats = $backupService->getStats();
        $backups = $backupService->list();

        // Display statistics
        $this->info('Backup Statistics:');
        $this->newLine();

        $this->table(['Metric', 'Value'], [
            ['Total Backups', $stats['total_backups']],
            ['Database Backups', $stats['database_backups']],
            ['Full Backups', $stats['full_backups']],
            ['Total Size', $stats['total_size']],
        ]);

        if ($stats['latest_backup']) {
            $this->newLine();
            $this->info('Latest Backup:');
            $this->table(['Property', 'Value'], [
                ['Filename', $stats['latest_backup']['filename']],
                ['Size', $stats['latest_backup']['size']],
                ['Type', $stats['latest_backup']['type']],
                ['Created', $stats['latest_backup']['created_at']->format('Y-m-d H:i:s')],
            ]);
        }

        // Display recent backups
        if ($backups->count() > 0) {
            $this->newLine();
            $this->info('Recent Backups:');
            $this->newLine();

            $tableData = $backups->take(10)->map(function ($backup) {
                return [
                    $backup['filename'],
                    $backup['type'],
                    $backup['size'],
                    $backup['created_at']->format('Y-m-d H:i:s'),
                ];
            })->toArray();

            $this->table(['Filename', 'Type', 'Size', 'Created At'], $tableData);
        }

        return self::SUCCESS;
    }
}
