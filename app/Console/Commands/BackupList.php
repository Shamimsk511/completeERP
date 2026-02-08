<?php

namespace App\Console\Commands;

use App\Services\BackupService;
use Illuminate\Console\Command;

class BackupList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:list
                            {--limit=20 : Maximum number of backups to display}
                            {--type= : Filter by type (database, full)}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'List all available backup files';

    /**
     * Execute the console command.
     */
    public function handle(BackupService $backupService)
    {
        $backups = $backupService->list();

        if ($backups->isEmpty()) {
            $this->info('No backup files found.');
            return self::SUCCESS;
        }

        // Filter by type if specified
        if ($this->option('type')) {
            $type = $this->option('type');
            $backups = $backups->filter(fn($b) => $b['type'] === $type);

            if ($backups->isEmpty()) {
                $this->info("No {$type} backups found.");
                return self::SUCCESS;
            }
        }

        // Display backups
        $limit = (int) $this->option('limit');
        $display = $backups->take($limit);

        $this->info("Showing " . $display->count() . " of " . $backups->count() . " backups:\n");

        $tableData = $display->map(function ($backup, $index) {
            return [
                $index + 1,
                $backup['filename'],
                $backup['type'],
                $backup['size'],
                $backup['created_at']->format('Y-m-d H:i:s'),
                $backup['created_at']->diffForHumans(),
            ];
        })->toArray();

        $this->table(['#', 'Filename', 'Type', 'Size', 'Created', 'Age'], $tableData);

        // Show summary
        $this->newLine();
        $stats = $backupService->getStats();
        $this->info("Total backups: {$stats['total_backups']} | Total size: {$stats['total_size']}");

        return self::SUCCESS;
    }
}
