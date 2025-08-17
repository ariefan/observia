<?php

namespace App\Http\Controllers;

use App\Models\LoginLog;
use App\Models\Farm;
use App\Traits\HasCurrentFarm;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LoginLogController extends Controller
{
    use HasCurrentFarm;

    /**
     * Display login logs for current farm or all farms (for super users)
     */
    public function index(Request $request)
    {
        // Handle super user farm selection
        $farmId = null;
        $allFarms = [];
        
        if (auth()->user()->is_super_user) {
            $selectedFarmId = $request->get('farm_id', 'all');
            
            // Get all farms for super user dropdown
            $allFarms = Farm::select('id', 'name', 'address')
                ->orderBy('name')
                ->get()
                ->toArray();
            
            if ($selectedFarmId !== 'all') {
                $farmId = $selectedFarmId;
            }
        } else {
            $farmId = $this->getCurrentFarmId();
        }

        $query = LoginLog::query()
            ->with(['user'])
            ->latest();
            
        // Apply farm filter
        if ($farmId) {
            $query->where('farm_id', $farmId);
        }

        // Filter by event type
        if ($request->has('event') && $request->event) {
            $query->forEvent($request->event);
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

        // Search in user name or email
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('user_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $loginLogs = $query->paginate(50)->withQueryString();

        // Get filter options
        $eventTypes = collect([
            ['value' => 'login', 'label' => 'Masuk'],
            ['value' => 'logout', 'label' => 'Keluar'],
            ['value' => 'failed_login', 'label' => 'Gagal Masuk'],
        ]);

        return Inertia::render('LoginLogs/Index', [
            'loginLogs' => $loginLogs,
            'filters' => $request->only(['event', 'user_id', 'date_from', 'date_to', 'search', 'farm_id']),
            'eventTypes' => $eventTypes,
            'allFarms' => $allFarms,
            'selectedFarmId' => auth()->user()->is_super_user ? $request->get('farm_id', 'all') : null,
            'isSuperUser' => auth()->user()->is_super_user,
        ]);
    }

    /**
     * Show detailed view of a specific login log
     */
    public function show(LoginLog $loginLog)
    {
        // Super users can view any login log, regular users only their farm's logs
        if (!auth()->user()->is_super_user) {
            if ($loginLog->farm_id !== $this->getCurrentFarmId()) {
                abort(403, 'Unauthorized access to login log.');
            }
        }

        $loginLog->load(['user', 'farm']);

        return Inertia::render('LoginLogs/Show', [
            'loginLog' => $loginLog,
        ]);
    }

    /**
     * Export login logs
     */
    public function export(Request $request)
    {
        $farmId = null;
        
        if (auth()->user()->is_super_user) {
            $selectedFarmId = $request->get('farm_id', 'all');
            if ($selectedFarmId !== 'all') {
                $farmId = $selectedFarmId;
            }
        } else {
            $farmId = $this->getCurrentFarmId();
        }

        $query = LoginLog::query()
            ->with(['user'])
            ->latest();
            
        // Apply farm filter
        if ($farmId) {
            $query->where('farm_id', $farmId);
        }

        // Apply same filters as index
        if ($request->has('event') && $request->event) {
            $query->forEvent($request->event);
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

        $loginLogs = $query->limit(1000)->get(); // Limit to prevent memory issues

        $csvData = [];
        $csvData[] = [
            'Tanggal',
            'Pengguna',
            'Email',
            'Event',
            'Alamat IP',
            'Peternakan',
        ];

        foreach ($loginLogs as $log) {
            $csvData[] = [
                $log->created_at->format('Y-m-d H:i:s'),
                $log->user_name ?? 'Unknown',
                $log->email ?? '-',
                $log->event_name,
                $log->ip_address ?? '-',
                $log->farm ? $log->farm->name : '-',
            ];
        }

        $filename = 'login_logs_' . date('Y-m-d_H-i-s') . '.csv';

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
