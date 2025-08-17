<?php

namespace App\Http\Controllers;

use App\Models\Audit;
use App\Models\Farm;
use App\Traits\HasCurrentFarm;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AuditController extends Controller
{
    use HasCurrentFarm;

    /**
     * Display audit trail for current farm or all farms (for super users)
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

        $query = Audit::query()
            ->with(['user'])
            ->latest();
            
        // Apply farm filter
        if ($farmId) {
            $query->where('farm_id', $farmId);
        }

        // Filter by model type
        if ($request->has('model') && $request->model) {
            $query->forModel($request->model);
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

        // Search in model name or event
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('auditable_type', 'like', "%{$search}%")
                  ->orWhere('event', 'like', "%{$search}%")
                  ->orWhere('user_name', 'like', "%{$search}%");
            });
        }

        $audits = $query->paginate(50)->withQueryString();

        // Get filter options
        $modelTypesQuery = Audit::query()->distinct();
        
        if ($farmId) {
            $modelTypesQuery->where('farm_id', $farmId);
        }
        
        $modelTypes = $modelTypesQuery->pluck('auditable_type')
            ->map(function ($type) {
                $baseName = class_basename($type);
                $indonesianName = match($baseName) {
                    'User' => 'Pengguna',
                    'Livestock' => 'Ternak',
                    'Herd' => 'Kandang',
                    'Feed' => 'Pakan',
                    'Ration' => 'Ransum',
                    'RationItem' => 'Item Ransum',
                    'HerdFeeding' => 'Pemberian Pakan',
                    'LivestockWeight' => 'Berat Ternak',
                    'LivestockMilking' => 'Pemerahan',
                    'FeedingLeftover' => 'Sisa Pakan',
                    'Farm' => 'Peternakan',
                    'HistoryRation' => 'Riwayat Ransum',
                    'HistoryRationItem' => 'Riwayat Item Ransum',
                    default => $baseName,
                };
                
                return [
                    'value' => $type,
                    'label' => $indonesianName
                ];
            });

        $eventTypes = collect([
            ['value' => 'created', 'label' => 'Dibuat'],
            ['value' => 'updated', 'label' => 'Diubah'],
            ['value' => 'deleted', 'label' => 'Dihapus'],
            ['value' => 'restored', 'label' => 'Dipulihkan'],
        ]);

        return Inertia::render('Audits/Index', [
            'audits' => $audits,
            'filters' => $request->only(['model', 'event', 'user_id', 'date_from', 'date_to', 'search', 'farm_id']),
            'modelTypes' => $modelTypes,
            'eventTypes' => $eventTypes,
            'allFarms' => $allFarms,
            'selectedFarmId' => auth()->user()->is_super_user ? $request->get('farm_id', 'all') : null,
            'isSuperUser' => auth()->user()->is_super_user,
        ]);
    }

    /**
     * Show detailed view of a specific audit
     */
    public function show(Audit $audit)
    {
        // Super users can view any audit, regular users only their farm's audits
        if (!auth()->user()->is_super_user) {
            if ($audit->farm_id !== $this->getCurrentFarmId()) {
                abort(403, 'Unauthorized access to audit record.');
            }
        }

        $audit->load(['user', 'auditable']);

        return Inertia::render('Audits/Show', [
            'audit' => $audit,
        ]);
    }

    /**
     * Get audit trail for a specific model
     */
    public function model(Request $request, string $modelType, string $modelId)
    {
        $audits = Audit::query()
            ->where('auditable_type', $modelType)
            ->where('auditable_id', $modelId)
            ->where('farm_id', $this->getCurrentFarmId())
            ->with(['user'])
            ->latest()
            ->paginate(20);

        return response()->json([
            'audits' => $audits,
            'model_name' => class_basename($modelType),
        ]);
    }

    /**
     * Export audit trail
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

        $query = Audit::query()
            ->with(['user'])
            ->latest();
            
        // Apply farm filter
        if ($farmId) {
            $query->where('farm_id', $farmId);
        }

        // Apply same filters as index
        if ($request->has('model') && $request->model) {
            $query->forModel($request->model);
        }

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

        $audits = $query->limit(1000)->get(); // Limit to prevent memory issues

        $csvData = [];
        $csvData[] = [
            'Tanggal',
            'Pengguna',
            'Model',
            'ID Model',
            'Event',
            'Perubahan',
            'Alamat IP',
        ];

        foreach ($audits as $audit) {
            $changes = '';
            if ($audit->formatted_changes) {
                $changeStrings = [];
                foreach ($audit->formatted_changes as $field => $change) {
                    $changeStrings[] = "{$field}: {$change['old']} → {$change['new']}";
                }
                $changes = implode('; ', $changeStrings);
            }

            $csvData[] = [
                $audit->created_at->format('Y-m-d H:i:s'),
                $audit->user_name ?? 'System',
                $audit->model_name,
                $audit->auditable_id,
                $audit->event_name,
                $changes,
                $audit->ip_address,
            ];
        }

        $filename = 'audit_trail_' . date('Y-m-d_H-i-s') . '.csv';

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
