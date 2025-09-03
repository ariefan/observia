<?php

namespace App\Console\Commands;

use App\Services\BackupService;
use Illuminate\Console\Command;

class CreateBackup extends Command
{
    protected $signature = 'backup:create';
    protected $description = 'Create a backup of the database and storage files';

    public function handle()
    {
        $this->info('Starting backup process...');

        $backupService = new BackupService();
        $success = $backupService->createBackup();

        if ($success) {
            $this->info('Backup completed successfully!');
            return 0;
        } else {
            $this->error('Backup failed. Check the logs for more details.');
            return 1;
        }
    }
}