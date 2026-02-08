<?php

namespace App\Console\Commands;

use App\Services\BackupService;
use Illuminate\Console\Command;

class BackupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:database
                            {--no-cleanup : Do not cleanup old backups}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Create a database-only backup (optimal for shared hosting)';

    /**
     * Execute the console command.
     */
    public function handle(BackupService $backupService)
    {
        $this->info('Starting database backup...');

        $result = $backupService->create();

        if ($result['success']) {
            $this->info("✓ Database backup created successfully!");
            $this->table(['Property', 'Value'], [
                ['Filename', $result['filename']],
                ['Size', $result['size']],
                ['Type', $result['type']],
            ]);

            // Optional: Show backup location
            $this->newLine();
            $this->info("Location: {$result['path']}");

            return self::SUCCESS;
        }

        $this->error("✗ Backup failed: {$result['message']}");
        return self::FAILURE;
    }
}
