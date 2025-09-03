<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Setting;

return new class extends Migration
{
    public function up()
    {
        $backupSettings = [
            [
                'key' => 'backup_enabled',
                'label' => 'Enable Automatic Backups',
                'value' => 'false',
                'type' => 'boolean',
                'description' => 'Enable or disable automatic daily backups',
                'category' => 'Backup',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'key' => 'backup_time',
                'label' => 'Backup Time',
                'value' => '01:00',
                'type' => 'text',
                'description' => 'Time to run daily backups (24-hour format, e.g., 01:00)',
                'category' => 'Backup',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'key' => 'backup_upload_to_google_drive',
                'label' => 'Upload to Google Drive',
                'value' => 'false',
                'type' => 'boolean',
                'description' => 'Upload backups to Google Drive',
                'category' => 'Backup',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'key' => 'backup_google_service_account_path',
                'label' => 'Google Service Account JSON Path',
                'value' => '',
                'type' => 'text',
                'description' => 'Full path to Google Service Account JSON file (e.g., /path/to/service-account.json)',
                'category' => 'Backup',
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'key' => 'backup_google_drive_folder_id',
                'label' => 'Google Drive Folder ID',
                'value' => '',
                'type' => 'text',
                'description' => 'Google Drive folder ID where backups will be stored (optional)',
                'category' => 'Backup',
                'sort_order' => 5,
                'is_active' => true,
            ],
            [
                'key' => 'backup_keep_local',
                'label' => 'Keep Local Backups',
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Keep backup files locally on server',
                'category' => 'Backup',
                'sort_order' => 6,
                'is_active' => true,
            ],
            [
                'key' => 'backup_keep_days',
                'label' => 'Keep Backups For Days',
                'value' => '30',
                'type' => 'number',
                'description' => 'Number of days to keep backups (both local and Google Drive)',
                'category' => 'Backup',
                'sort_order' => 7,
                'is_active' => true,
            ],
            [
                'key' => 'backup_include_database',
                'label' => 'Include Database in Backup',
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Include database dump in backup',
                'category' => 'Backup',
                'sort_order' => 8,
                'is_active' => true,
            ],
            [
                'key' => 'backup_include_storage',
                'label' => 'Include Storage Files in Backup',
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Include storage/app files in backup',
                'category' => 'Backup',
                'sort_order' => 9,
                'is_active' => true,
            ],
        ];

        foreach ($backupSettings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }

    public function down()
    {
        $keys = [
            'backup_enabled',
            'backup_time',
            'backup_upload_to_google_drive',
            'backup_google_service_account_path',
            'backup_google_drive_folder_id',
            'backup_keep_local',
            'backup_keep_days',
            'backup_include_database',
            'backup_include_storage',
        ];

        Setting::whereIn('key', $keys)->delete();
    }
};