<?php

namespace App\Services;

use App\Models\Setting;
use Google_Client;
use Google_Service_Drive;
use Google_Service_Drive_DriveFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use ZipArchive;
use Exception;

class BackupService
{
    private $googleClient;
    private $driveService;

    public function __construct()
    {
        $this->initializeGoogleDrive();
    }

    private function initializeGoogleDrive()
    {
        try {
            $serviceAccountPath = Setting::getValue('backup_google_service_account_path');

            if (!$serviceAccountPath) {
                Log::info('Google Drive not configured - service account path not set');
                return false;
            }

            // Resolve relative paths from Laravel root
            if (!str_starts_with($serviceAccountPath, '/') && !str_contains($serviceAccountPath, ':\\')) {
                $serviceAccountPath = base_path($serviceAccountPath);
            }

            if (!file_exists($serviceAccountPath)) {
                Log::info('Google Drive service account file not found at: ' . $serviceAccountPath);
                return false;
            }

            // Check if Google Drive classes are available
            if (!class_exists('Google_Service_Drive') && !class_exists('Google\\Service\\Drive')) {
                Log::warning('Google Drive API classes not found. Please ensure google/apiclient and google/apiclient-services are installed.');
                return false;
            }

            $this->googleClient = new Google_Client();
            $this->googleClient->setAuthConfig($serviceAccountPath);
            
            // Try both class naming conventions
            if (class_exists('Google_Service_Drive')) {
                $this->googleClient->addScope(Google_Service_Drive::DRIVE_FILE);
                $this->driveService = new Google_Service_Drive($this->googleClient);
            } elseif (class_exists('Google\\Service\\Drive')) {
                $this->googleClient->addScope('https://www.googleapis.com/auth/drive.file');
                $driveClass = 'Google\\Service\\Drive';
                $this->driveService = new $driveClass($this->googleClient);
            } else {
                throw new Exception('Google Drive service class not found');
            }

            return true;
        } catch (Exception $e) {
            Log::error('Google Drive initialization failed: ' . $e->getMessage());
            return false;
        }
    }

    public function createBackup()
    {
        try {
            $backupEnabled = Setting::getValue('backup_enabled', false);
            if (!$backupEnabled) {
                return false;
            }

            Log::info('Starting backup process');

            // Create database backup
            $dbBackupPath = $this->createDatabaseBackup();
            
            // Create storage backup
            $storageBackupPath = $this->createStorageBackup();
            
            // Create combined backup archive
            $combinedBackupPath = $this->createCombinedBackup($dbBackupPath, $storageBackupPath);

            // Upload to Google Drive if configured
            $uploadToGoogleDrive = Setting::getValue('backup_upload_to_google_drive', false);
            if ($uploadToGoogleDrive && $this->driveService) {
                $this->uploadToGoogleDrive($combinedBackupPath);
            }

            // Clean up local temporary files
            if (file_exists($dbBackupPath)) unlink($dbBackupPath);
            if (file_exists($storageBackupPath)) unlink($storageBackupPath);

            // Keep local backup based on settings
            $keepLocalBackup = Setting::getValue('backup_keep_local', true);
            if (!$keepLocalBackup && file_exists($combinedBackupPath)) {
                unlink($combinedBackupPath);
            }

            Log::info('Backup process completed successfully');
            return true;

        } catch (Exception $e) {
            Log::error('Backup process failed: ' . $e->getMessage());
            return false;
        }
    }

    private function createDatabaseBackup()
    {
        $backupPath = storage_path('backups');
        if (!file_exists($backupPath)) {
            mkdir($backupPath, 0755, true);
        }

        $filename = 'database_backup_' . date('Y-m-d_H-i-s') . '.sql';
        $filePath = $backupPath . '/' . $filename;

        $dbConfig = config('database.connections.' . config('database.default'));
        $driver = $dbConfig['driver'] ?? config('database.default');
        
        try {
            // Support both MySQL and PostgreSQL
            if ($driver === 'pgsql') {
                // PostgreSQL backup using pg_dump
                $pgDumpPath = $this->findPgDumpPath();
                if (!$pgDumpPath) {
                    throw new Exception('pg_dump not found. Please install PostgreSQL client tools.');
                }
                
                $command = sprintf(
                    'PGPASSWORD=%s %s -h %s -U %s -d %s > %s',
                    escapeshellarg($dbConfig['password']),
                    $pgDumpPath,
                    escapeshellarg($dbConfig['host']),
                    escapeshellarg($dbConfig['username']),
                    escapeshellarg($dbConfig['database']),
                    escapeshellarg($filePath)
                );
            } else {
                // MySQL backup using mysqldump
                $command = sprintf(
                    'mysqldump -h%s -u%s -p%s %s > %s',
                    escapeshellarg($dbConfig['host']),
                    escapeshellarg($dbConfig['username']),
                    escapeshellarg($dbConfig['password']),
                    escapeshellarg($dbConfig['database']),
                    escapeshellarg($filePath)
                );
            }

            exec($command, $output, $returnCode);

            if ($returnCode !== 0) {
                throw new Exception("Database backup failed with return code: $returnCode. Output: " . implode("\n", $output));
            }

        } catch (Exception $e) {
            // Fallback: Create a simple database export using Laravel
            Log::warning('Native database backup failed, using Laravel fallback: ' . $e->getMessage());
            $this->createDatabaseBackupFallback($filePath);
        }

        return $filePath;
    }

    private function findPgDumpPath()
    {
        // Common PostgreSQL installation paths on Windows
        $possiblePaths = [
            'pg_dump', // If in PATH
            'C:\\Program Files\\PostgreSQL\\15\\bin\\pg_dump.exe',
            'C:\\Program Files\\PostgreSQL\\14\\bin\\pg_dump.exe',
            'C:\\Program Files\\PostgreSQL\\13\\bin\\pg_dump.exe',
            'C:\\Program Files (x86)\\PostgreSQL\\15\\bin\\pg_dump.exe',
            'C:\\Program Files (x86)\\PostgreSQL\\14\\bin\\pg_dump.exe',
        ];

        foreach ($possiblePaths as $path) {
            if ($path === 'pg_dump') {
                // Test if it's in PATH
                exec('where pg_dump', $output, $returnCode);
                if ($returnCode === 0) {
                    return 'pg_dump';
                }
            } elseif (file_exists($path)) {
                return $path;
            }
        }

        return null;
    }

    private function createDatabaseBackupFallback($filePath)
    {
        // Simple fallback: export all table structures and data using Laravel
        $content = "-- Database backup created by AIFarm Backup Service\n";
        $content .= "-- Date: " . date('Y-m-d H:i:s') . "\n\n";

        $tables = DB::select("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'");
        
        foreach ($tables as $table) {
            $tableName = $table->table_name;
            $content .= "-- Table: $tableName\n";
            
            // Get table data
            $rows = DB::table($tableName)->get();
            foreach ($rows as $row) {
                $values = array_map(function($value) {
                    return is_null($value) ? 'NULL' : "'" . addslashes($value) . "'";
                }, (array) $row);
                
                $content .= "INSERT INTO $tableName (" . implode(', ', array_keys((array) $row)) . ") VALUES (" . implode(', ', $values) . ");\n";
            }
            $content .= "\n";
        }

        file_put_contents($filePath, $content);
    }

    private function createStorageBackup()
    {
        $backupPath = storage_path('backups');
        $filename = 'storage_backup_' . date('Y-m-d_H-i-s') . '.zip';
        $filePath = $backupPath . '/' . $filename;

        $zip = new ZipArchive();
        if ($zip->open($filePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
            throw new Exception('Cannot create storage backup zip file');
        }

        // Add storage/app files (excluding backups and temp folders)
        $this->addDirectoryToZip($zip, storage_path('app'), 'storage/app', [
            'backups',
            'backup-temp',
            '.gitignore'
        ]);

        $zip->close();
        return $filePath;
    }

    private function addDirectoryToZip($zip, $directory, $localPath, $excludes = [])
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            $filePath = $file->getRealPath();
            $relativePath = $localPath . '/' . substr($filePath, strlen($directory) + 1);
            $relativePath = str_replace('\\', '/', $relativePath);

            // Check if file/directory should be excluded
            $shouldExclude = false;
            foreach ($excludes as $exclude) {
                if (strpos($relativePath, $exclude) !== false) {
                    $shouldExclude = true;
                    break;
                }
            }

            if (!$shouldExclude) {
                if ($file->isDir()) {
                    $zip->addEmptyDir($relativePath);
                } else {
                    $zip->addFile($filePath, $relativePath);
                }
            }
        }
    }

    private function createCombinedBackup($dbBackupPath, $storageBackupPath)
    {
        $backupPath = storage_path('backups');
        $filename = 'full_backup_' . date('Y-m-d_H-i-s') . '.zip';
        $filePath = $backupPath . '/' . $filename;

        $zip = new ZipArchive();
        if ($zip->open($filePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
            throw new Exception('Cannot create combined backup zip file');
        }

        $zip->addFile($dbBackupPath, 'database.sql');
        $zip->addFile($storageBackupPath, 'storage.zip');
        
        // Add backup metadata
        $metadata = [
            'created_at' => now()->toISOString(),
            'app_name' => config('app.name'),
            'laravel_version' => app()->version(),
            'php_version' => phpversion(),
            'database_driver' => config('database.default'),
        ];
        $zip->addFromString('backup_info.json', json_encode($metadata, JSON_PRETTY_PRINT));

        $zip->close();
        return $filePath;
    }

    private function uploadToGoogleDrive($filePath)
    {
        try {
            $folderId = Setting::getValue('backup_google_drive_folder_id');
            
            $fileMetadata = new Google_Service_Drive_DriveFile([
                'name' => basename($filePath),
                'parents' => $folderId ? [$folderId] : null
            ]);

            $content = file_get_contents($filePath);
            $file = $this->driveService->files->create($fileMetadata, [
                'data' => $content,
                'mimeType' => 'application/zip',
                'uploadType' => 'multipart',
                'fields' => 'id'
            ]);

            Log::info('Backup uploaded to Google Drive successfully', ['file_id' => $file->id]);
            
            // Clean up old backups on Google Drive
            $this->cleanupOldGoogleDriveBackups();

        } catch (Exception $e) {
            Log::error('Failed to upload backup to Google Drive: ' . $e->getMessage());
            throw $e;
        }
    }

    private function cleanupOldGoogleDriveBackups()
    {
        try {
            $keepDays = (int) Setting::getValue('backup_keep_days', 30);
            $folderId = Setting::getValue('backup_google_drive_folder_id');
            
            $query = "name contains 'full_backup_' and trashed = false";
            if ($folderId) {
                $query .= " and '$folderId' in parents";
            }

            $files = $this->driveService->files->listFiles([
                'q' => $query,
                'orderBy' => 'createdTime desc',
                'fields' => 'files(id,name,createdTime)'
            ]);

            $cutoffDate = now()->subDays($keepDays);

            foreach ($files->getFiles() as $file) {
                $createdTime = new \DateTime($file->getCreatedTime());
                if ($createdTime < $cutoffDate) {
                    $this->driveService->files->delete($file->getId());
                    Log::info('Deleted old backup from Google Drive', ['file_name' => $file->getName()]);
                }
            }

        } catch (Exception $e) {
            Log::warning('Failed to cleanup old Google Drive backups: ' . $e->getMessage());
        }
    }

    public function testGoogleDriveConnection()
    {
        try {
            $serviceAccountPath = Setting::getValue('backup_google_service_account_path');
            
            if (!$serviceAccountPath) {
                return [
                    'success' => false, 
                    'message' => 'Google Service Account JSON path not configured. Please set the path in backup settings.'
                ];
            }

            // Resolve relative paths from Laravel root
            if (!str_starts_with($serviceAccountPath, '/') && !str_contains($serviceAccountPath, ':\\')) {
                $serviceAccountPath = base_path($serviceAccountPath);
            }
            
            if (!file_exists($serviceAccountPath)) {
                return [
                    'success' => false, 
                    'message' => "Google Service Account JSON file not found at: $serviceAccountPath"
                ];
            }

            if (!$this->driveService) {
                return [
                    'success' => false, 
                    'message' => 'Google Drive service initialization failed. Please check the service account JSON file.'
                ];
            }

            $about = $this->driveService->about->get(['fields' => 'user,storageQuota']);
            
            return [
                'success' => true,
                'message' => 'Connection successful',
                'user_email' => $about->getUser()->getEmailAddress(),
                'storage_used' => number_format($about->getStorageQuota()->getUsage() / 1024 / 1024 / 1024, 2) . ' GB',
                'storage_limit' => $about->getStorageQuota()->getLimit() ? number_format($about->getStorageQuota()->getLimit() / 1024 / 1024 / 1024, 2) . ' GB' : 'Unlimited'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage()
            ];
        }
    }

    public function cleanupLocalBackups()
    {
        try {
            $backupPath = storage_path('backups');
            $keepDays = (int) Setting::getValue('backup_keep_days', 30);
            $cutoffTime = time() - ($keepDays * 24 * 60 * 60);

            if (!is_dir($backupPath)) {
                return;
            }

            $files = glob($backupPath . '/*');
            foreach ($files as $file) {
                if (is_file($file) && filemtime($file) < $cutoffTime) {
                    unlink($file);
                    Log::info('Deleted old local backup', ['file' => basename($file)]);
                }
            }

        } catch (Exception $e) {
            Log::error('Failed to cleanup local backups: ' . $e->getMessage());
        }
    }
}