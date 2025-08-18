<?php

namespace App\Http\Controllers;

use App\Models\ErrorLog;
use App\Models\Farm;
use App\Traits\HasCurrentFarm;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ErrorLogController extends Controller
{
    use HasCurrentFarm;

    public function index(Request $request)
    {
        // Only super users can access error logs
        if (!auth()->user()->is_super_user) {
            abort(403, 'Unauthorized access to error logs.');
        }

        // Handle super user farm selection
        $farmId = null;
        $allFarms = [];
        
        $selectedFarmId = $request->get('farm_id', 'all');
        
        // Get all farms for super user dropdown
        $allFarms = Farm::select('id', 'name', 'address')
            ->orderBy('name')
            ->get()
            ->toArray();
        
        if ($selectedFarmId !== 'all') {
            $farmId = $selectedFarmId;
        }

        $query = ErrorLog::query()
            ->with(['user', 'farm'])
            ->latest();
            
        // Apply farm filter
        if ($farmId) {
            $query->where('farm_id', $farmId);
        }

        // Filter by level
        if ($request->has('level') && $request->level) {
            $query->forLevel($request->level);
        }

        // Filter by user
        if ($request->has('user_id') && $request->user_id) {
            $query->forUser($request->user_id);
        }

        // Filter by date range
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search in message, file, or user name
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('message', 'like', "%{$search}%")
                  ->orWhere('file', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $errorLogs = $query->paginate(50)->withQueryString();

        // Get filter options
        $errorLevels = collect([
            ['value' => 'emergency', 'label' => 'Darurat'],
            ['value' => 'alert', 'label' => 'Peringatan'],
            ['value' => 'critical', 'label' => 'Kritis'],
            ['value' => 'error', 'label' => 'Error'],
            ['value' => 'warning', 'label' => 'Peringatan'],
            ['value' => 'notice', 'label' => 'Pemberitahuan'],
            ['value' => 'info', 'label' => 'Informasi'],
            ['value' => 'debug', 'label' => 'Debug'],
        ]);

        return Inertia::render('ErrorLogs/Index', [
            'errorLogs' => $errorLogs,
            'filters' => $request->only(['level', 'user_id', 'date_from', 'date_to', 'search', 'farm_id']),
            'errorLevels' => $errorLevels,
            'allFarms' => $allFarms,
            'selectedFarmId' => $request->get('farm_id', 'all'),
        ]);
    }

    public function show(ErrorLog $errorLog)
    {
        // Only super users can access error logs
        if (!auth()->user()->is_super_user) {
            abort(403, 'Unauthorized access to error log.');
        }

        $errorLog->load(['user', 'farm']);

        return Inertia::render('ErrorLogs/Show', [
            'errorLog' => $errorLog,
        ]);
    }

    public function export(Request $request)
    {
        // Only super users can export error logs
        if (!auth()->user()->is_super_user) {
            abort(403, 'Unauthorized access to error logs.');
        }

        $farmId = null;
        $selectedFarmId = $request->get('farm_id', 'all');
        if ($selectedFarmId !== 'all') {
            $farmId = $selectedFarmId;
        }

        $query = ErrorLog::query()
            ->with(['user', 'farm'])
            ->latest();
            
        // Apply farm filter
        if ($farmId) {
            $query->where('farm_id', $farmId);
        }

        // Apply same filters as index
        if ($request->has('level') && $request->level) {
            $query->forLevel($request->level);
        }

        if ($request->has('user_id') && $request->user_id) {
            $query->forUser($request->user_id);
        }

        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $errorLogs = $query->limit(1000)->get(); // Limit to prevent memory issues

        $csvData = [];
        $csvData[] = [
            'Tanggal',
            'Level',
            'Pesan Error',
            'File',
            'Baris',
            'Pengguna',
            'Email',
            'Alamat IP',
            'URL',
            'Peternakan',
        ];

        foreach ($errorLogs as $log) {
            $csvData[] = [
                $log->created_at->format('Y-m-d H:i:s'),
                $log->level_name,
                $log->message,
                $log->file ?? '-',
                $log->line ?? '-',
                $log->user ? $log->user->name : '-',
                $log->user ? $log->user->email : '-',
                $log->ip_address ?? '-',
                $log->url ?? '-',
                $log->farm ? $log->farm->name : '-',
            ];
        }

        $filename = 'error_logs_' . date('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($csvData) {
            $file = fopen('php://output', 'w');
            foreach ($csvData as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
