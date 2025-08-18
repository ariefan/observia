<?php

namespace App\Http\Controllers;

use App\Models\Livestock;
use App\Models\Species;
use App\Models\Breed;
use App\Http\Requests\StoreLivestockRequest;
use App\Http\Requests\UpdateLivestockRequest;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\LivestockWeight;
use App\Models\LivestockMilking;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Traits\HasCurrentFarm;
use App\Services\FileUploadService;

class LivestockController extends Controller
{
    use AuthorizesRequests;
    use HasCurrentFarm;

    protected $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $currentFarmId = $this->getCurrentFarmId();
        
        if (!$currentFarmId) {
            return Inertia::render('livestocks/Index', [
                'livestocks' => [
                    'data' => [],
                    'links' => [],
                    'total' => 0,
                ],
                'male_count' => 0,
                'female_count' => 0,
                'filters' => [],
            ]);
        }
        
        $query = Livestock::query()
            ->where('farm_id', $currentFarmId)
            ->with(['breed.species'])
            ->orderBy('created_at', 'desc');

        // Filter by status - only show active livestock unless specifically filtering for ended ones
        if ($request->filled('status')) {
            if ($request->status === 'ended') {
                // Show livestock that have endings
                $query->whereHas('endings');
            } else if ($request->status === 'active') {
                // Show only active livestock
                $query->where('status', 1);
            }
        } else {
            // Default: only show active livestock
            $query->where('status', 1);
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('aifarm_id', 'like', "%{$search}%")
                  ->orWhere('tag_id', 'like', "%{$search}%")
                  ->orWhereHas('breed', function ($breedQuery) use ($search) {
                      $breedQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Gender filter
        if ($request->filled('gender') && in_array($request->gender, ['M', 'F'])) {
            $query->where('sex', $request->gender);
        }

        // Paginate results
        $livestocks = $query->paginate(12)->withQueryString();

        // Get counts for active livestock only
        $activeLivestocks = Livestock::where('farm_id', $currentFarmId)
            ->where('status', 1)
            ->get();
        
        $male_count = $activeLivestocks->where('sex', 'M')->count();
        $female_count = $activeLivestocks->where('sex', 'F')->count();

        return Inertia::render('livestocks/Index', [
            'livestocks' => $livestocks,
            'male_count' => $male_count,
            'female_count' => $female_count,
            'filters' => $request->only(['search', 'gender', 'status']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $species = Species::query()->select('id', 'name')->get();
        $livestock = new Livestock();
        $livestock->photo = [];

        return Inertia::render('livestocks/Form', [
            'livestock' => $livestock,
            'species' => $species,
            'male_livestock' => '',
            'female_livestock' => '',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLivestockRequest $request)
    {
        $validated = $request->validated();
        $validated['farm_id'] = $this->getCurrentFarmId();

        // Remove photo from validated data before filling the model
        if (isset($validated['photo'])) {
            unset($validated['photo']);
        }

        $livestock = new Livestock();
        $livestock->fill($validated);

        // Process photos using the file upload service
        $photos = [];
        if (! is_null($request->photo) && ! empty($request->photo)) {
            $photos = $this->fileUploadService->processMixedFiles(
                $request->photo,
                'livestocks'
            );
        }

        $countLivestock = Livestock::query()->count();
        $livestock->photo = $photos;
        $livestock->aifarm_id = Livestock::generateAifarmId($countLivestock);

        if (!empty($livestock->herd_id)) {
            $livestock->herd_entry_date = now();
        }

        $livestock->save();

        // Automatically create initial weight record
        if (isset($validated['weight'])) {
            $livestock->weights()->create([
                'weight' => $validated['weight'],
                'date' => now(),
                'user_id' => auth()->id(),
            ]);
            // Update livestock's current weight
            $livestock->update(['weight' => $validated['weight']]);
        }

        return redirect()->route('livestocks.show', $livestock);
    }

    /**
     * Display the specified resource.
     */
    public function show(Livestock $livestock)
    {
        $this->authorize('view', $livestock);
        
        $livestock->load('breed.species', 'maleParent', 'femaleParent', 'herd');

        // Get weight history for the last 12 months (average per month, fill missing with last known)
        $now = now();
        $weights = $livestock->weights()
            ->where('date', '>=', $now->copy()->subMonths(12)->startOfMonth())
            ->orderBy('date')
            ->get();

        // Group weights by month for the current livestock
        $weightByMonth = $weights->groupBy(function($weight) {
            return \Carbon\Carbon::parse($weight->date)->format('Y-m');
        })->map(function($monthWeights) {
            // Average only for weights in this month
            return round($monthWeights->avg('weight'), 2);
        });

        $weightHistory = collect();
        $lastAvg = null;
        for ($i = 11; $i >= 0; $i--) {
            $month = $now->copy()->subMonths($i)->format('Y-m');
            $date = $now->copy()->subMonths($i)->endOfMonth()->toDateString();
            if (isset($weightByMonth[$month]) && $weightByMonth[$month] !== null) {
                $lastAvg = $weightByMonth[$month];
            }
            $weightHistory->push([
                'month' => $month,
                'average_weight' => $lastAvg !== null ? $lastAvg : 0,
                'date' => $date,
            ]);
        }

        // Get milking history for the last 12 months
        $milkingHistory = $livestock->milkings()
            ->where('date', '>=', now()->subMonths(12))
            ->orderBy('date')
            ->get()
            ->groupBy(function($milking) {
            return \Carbon\Carbon::parse($milking->date)->format('Y-m');
            })
            ->map(function($monthMilkings) {
            // Group by day within the month
            $dailyTotals = $monthMilkings->groupBy('date')->map(function($dayMilkings) {
                return $dayMilkings->sum('milk_volume');
            });

            $daysCount = $dailyTotals->count();
            $totalVolume = $dailyTotals->sum();

            return [
                'date' => $monthMilkings->first()->date,
                'average_volume' => $daysCount > 0 ? round($totalVolume / $daysCount, 2) : 0,
                'total_volume' => $totalVolume,
                'count' => $daysCount,
            ];
            })
            ->values();

        // Calculate unique lactation days
        $lactationDays = $livestock->milkings()->distinct('date')->count('date');

        // --- Calculate ranking by average litre per day for same species ---
        $speciesId = $livestock->breed->species->id;
        $allLivestocks = \App\Models\Livestock::whereHas('breed', function($q) use ($speciesId) {
            $q->where('species_id', $speciesId);
        })->with(['milkings'])->get();

        $livestockAverages = $allLivestocks->map(function($ls) {
            $lactationDays = $ls->milkings->unique('date')->count('date');
            $totalVolume = $ls->milkings->sum('milk_volume');
            $avg = $lactationDays > 0 ? $totalVolume / $lactationDays : 0;
            return [
                'id' => $ls->id,
                'average_litre_per_day' => $avg,
            ];
        })->sortByDesc('average_litre_per_day')->values();

        $rank = $livestockAverages->search(function($item) use ($livestock) {
            return $item['id'] == $livestock->id;
        });
        $rank = $rank !== false ? $rank + 1 : null;
        $totalRanked = $livestockAverages->count();
        // --- End ranking ---

        // Get feeding history from HerdFeeding
        $feedingHistory = \App\Models\HerdFeeding::whereHas('herd.livestocks', function($q) use ($livestock) {
            $q->where('livestocks.id', $livestock->id);
        })
        ->with(['ration.rationItems', 'user', 'leftover', 'herd.livestocks'])
        ->orderBy('date', 'desc')
        ->orderBy('time', 'desc')
        ->take(10)
        ->get()
        ->map(function ($feeding) {
            $feeding->livestock_count = $feeding->herd && $feeding->herd->livestocks ? $feeding->herd->livestocks->count() : 1;
            return $feeding;
        });

        // Get pedigree data
        $pedigreeData = \App\Models\Livestock::pedigree($livestock->id);

        // Get latest livestock ending (if any)
        $latestEnding = $livestock->endings()
            ->orderBy('ending_date', 'desc')
            ->first();

        return Inertia::render('livestocks/Show', [
            'livestock' => $livestock,
            'weightHistory' => $weightHistory,
            'milkingHistory' => $milkingHistory,
            'lactationDays' => $lactationDays,
            'rank' => $rank,
            'totalRanked' => $totalRanked,
            'feedingHistory' => $feedingHistory,
            'pedigreeData' => $pedigreeData,
            'latestEnding' => $latestEnding,
        ]);
    }

    public function weighting()
    {
        return Inertia::render('livestocks/Weighting', [
            'livestock' => new Livestock(),
        ]);
    }

    public function storeWeight(Request $request)
    {
        $validated = $request->validate([
            'livestock_id' => 'required|exists:livestocks,id',
            'weight' => 'required|numeric',
            'date' => 'required|date',
        ]);

        $livestock = Livestock::find($validated['livestock_id']);

        $livestock->weights()->create([
            'weight' => $validated['weight'],
            'date' => $validated['date'],
            'user_id' => auth()->id(),
        ]);

        // Update only if the new date is greater than or equal to the latest weight date
        $latestWeight = $livestock->weights()->orderByDesc('date')->first();
        if (!$latestWeight || $validated['date'] >= $latestWeight->date) {
            $livestock->update(['weight' => $validated['weight']]);
        }

        return redirect()->route('livestocks.show', $livestock);
    }

    public function milking()
    {
        return Inertia::render('livestocks/Milking', [
            'livestock' => new Livestock(),
        ]);
    }

    public function storeMilking(Request $request)
    {
        $validated = $request->validate([
            'livestock_id' => 'required|exists:livestocks,id',
            'milk_volume' => 'required|numeric|min:0',
            'date' => 'required|date',
            'time' => 'nullable|date_format:H:i',
            'session' => 'nullable|in:morning,evening,afternoon,night,midnight,dawn',
            'notes' => 'nullable|string|max:1000',
        ]);

        $livestock = Livestock::find($validated['livestock_id']);

        // Check if milking already exists for this livestock, date, and session
        $existingMilking = $livestock->milkings()
            ->where('date', $validated['date'])
            ->where('session', $validated['session'])
            ->first();

        if ($existingMilking) {
            return back()->withErrors([
                'session' => 'Data perahan untuk sesi ' . $validated['session'] . ' pada tanggal ini sudah ada. Setiap sesi hanya boleh dilakukan sekali per hari.'
            ]);
        }

        $livestock->milkings()->create([
            'milk_volume' => $validated['milk_volume'],
            'date' => $validated['date'],
            'time' => $validated['time'] ?? null,
            'session' => $validated['session'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('livestocks.show', $livestock);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Livestock $livestock)
    {
        $species = Species::query()->select('id', 'name')->get();
        $male_livestock = $livestock->maleParent ? $livestock->maleParent->name : '';
        $female_livestock = $livestock->femaleParent ? $livestock->femaleParent->name : '';

        $livestock->load('breed.species');

        return Inertia::render('livestocks/Form', [
            'livestock' => $livestock,
            'species' => $species,
            'male_livestock' => $male_livestock,
            'female_livestock' => $female_livestock,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLivestockRequest $request, Livestock $livestock)
    {
        $this->authorize('update', $livestock);
        
        $validated = $request->validated();
        
        // Remove photo from validated data before filling the model
        if (isset($validated['photo'])) {
            unset($validated['photo']);
        }
        
        $livestock->fill($validated);

        // Process photos using the file upload service
        $photos = [];
        if (! is_null($request->photo) && ! empty($request->photo)) {
            $photos = $this->fileUploadService->processMixedFiles(
                $request->photo,
                'livestocks'
            );
        }

        // If herd_id is changed, update herd_entry_date to now
        if ($livestock->isDirty('herd_id')) {
            $livestock->herd_entry_date = now();
        }

        $livestock->photo = $photos;
        $livestock->save();
        return redirect()->route('livestocks.show', $livestock);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Livestock $livestock)
    {
        $this->authorize('delete', $livestock);
        
        $livestock->delete();
        return redirect()->route('livestocks.index');
    }

    public function search(Request $request)
    {
        $query = $request->input('q');
        $sex = $request->input('sex');

        $livestocks = Livestock::query()
            ->where('farm_id', $this->getCurrentFarmId())
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('aifarm_id', 'like', "%{$query}%")
                    ->orWhere('tag_id', 'like', "%{$query}%");
            });

        if ($sex) {
            $livestocks->where('sex', $sex);
        }

        $livestocks = $livestocks
            ->select('id', 'name', 'aifarm_id', 'tag_id')
            ->limit(10)
            ->get();

        return response()->json($livestocks);
    }

    /**
     * Get livestock rankings by milk production and weight
     */
    public function rankings(Request $request)
    {
        $currentFarmId = $this->getCurrentFarmId();
        
        // Get all livestocks in the current farm with necessary relationships
        $livestocks = Livestock::whereHas('breed.species')
            ->where('farm_id', $currentFarmId)
            ->with(['breed.species', 'milkings', 'weights'])
            ->get();
        
        // Calculate milk production rankings
        $milkRankings = $livestocks->map(function($livestock) {
            $lactationDays = $livestock->milkings->unique('date')->count();
            $totalVolume = $livestock->milkings->sum('milk_volume');
            $avgPerDay = $lactationDays > 0 ? $totalVolume / $lactationDays : 0;
            
            return [
                'id' => $livestock->id,
                'name' => $livestock->name,
                'aifarm_id' => $livestock->aifarm_id,
                'photo' => $livestock->photo ? (is_array($livestock->photo) && count($livestock->photo) > 0 ? $livestock->photo[0] : null) : null,
                'species' => $livestock->breed->species->name,
                'average_litre_per_day' => round($avgPerDay, 2),
                'total_volume' => $totalVolume,
                'lactation_days' => $lactationDays
            ];
        })
        ->filter(function($item) {
            return $item['average_litre_per_day'] > 0; // Only include livestock with milk production
        })
        ->sortByDesc('average_litre_per_day')
        ->values();
        
        // Calculate weight rankings
        $weightRankings = $livestocks->map(function($livestock) {
            $latestWeight = $livestock->weights->sortByDesc('date')->first();
            $currentWeight = $latestWeight ? $latestWeight->weight : $livestock->weight;
            
            return [
                'id' => $livestock->id,
                'name' => $livestock->name,
                'aifarm_id' => $livestock->aifarm_id,
                'photo' => $livestock->photo ? (is_array($livestock->photo) && count($livestock->photo) > 0 ? $livestock->photo[0] : null) : null,
                'species' => $livestock->breed->species->name,
                'current_weight' => $currentWeight ? round($currentWeight, 2) : 0
            ];
        })
        ->filter(function($item) {
            return $item['current_weight'] > 0; // Only include livestock with recorded weights
        })
        ->sortByDesc('current_weight')
        ->values();
        
        return response()->json([
            'milk_rankings' => $milkRankings->take(10), // Top 10
            'weight_rankings' => $weightRankings->take(10), // Top 10
        ]);
    }
}
