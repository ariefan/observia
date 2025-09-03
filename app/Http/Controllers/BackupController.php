<?php

namespace App\Http\Controllers;

use App\Services\BackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class BackupController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (!$request->user()->is_super_user) {
                abort(403, 'Unauthorized.');
            }
            return $next($request);
        });
    }

    public function createBackup(Request $request)
    {
        try {
            $backupService = new BackupService();
            $success = $backupService->createBackup();

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Backup berhasil dibuat'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Backup gagal dibuat, periksa log untuk detail'
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Manual backup failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function testGoogleDrive(Request $request)
    {
        try {
            $backupService = new BackupService();
            $result = $backupService->testGoogleDriveConnection();

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error testing Google Drive: ' . $e->getMessage()
            ], 500);
        }
    }

    public function cleanupBackups(Request $request)
    {
        try {
            $backupService = new BackupService();
            $backupService->cleanupLocalBackups();

            return response()->json([
                'success' => true,
                'message' => 'Backup lama berhasil dibersihkan'
            ]);
        } catch (\Exception $e) {
            Log::error('Backup cleanup failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getBackupStatus(Request $request)
    {
        try {
            $backupPath = storage_path('backups');
            $backups = [];

            if (is_dir($backupPath)) {
                $files = glob($backupPath . '/full_backup_*.zip');
                foreach ($files as $file) {
                    $backups[] = [
                        'name' => basename($file),
                        'size' => filesize($file),
                        'created_at' => date('Y-m-d H:i:s', filemtime($file))
                    ];
                }
                
                // Sort by creation time, newest first
                usort($backups, function($a, $b) {
                    return strtotime($b['created_at']) - strtotime($a['created_at']);
                });
            }

            return response()->json([
                'success' => true,
                'backups' => $backups,
                'backup_path' => $backupPath
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}