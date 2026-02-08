<?php

namespace App\Console\Commands;

use App\Services\BackupService;
use Illuminate\Console\Command;

class BackupFull extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:full
                            {--no-cleanup : Do not cleanup old backups}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Create a full backup including database and uploaded files (ZIP format)';

    /**
     * Execute the console command.
     */
    public function handle(BackupService $backupService)
    {
        $this->info('Starting full backup (database + files)...');

        $result = $backupService->createFull();

        if ($result['success']) {
            $this->info("✓ Full backup created successfully!");
            $this->table(['Property', 'Value'], [
                ['Filename', $result['filename']],
                ['Size', $result['size']],
                ['Files Included', $result['files_count'] ?? 0],
                ['Type', $result['type']],
            ]);

            // Optional: Show backup location
            $this->newLine();
            $this->info("Location: {$result['path']}");

            return self::SUCCESS;
        }

        $this->error("✗ Full backup failed: {$result['message']}");
        return self::FAILURE;
    }
}
