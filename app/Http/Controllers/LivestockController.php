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

class LivestockController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $livestocks = Livestock::query()
            ->where('farm_id', Auth::user()->current_farm_id)
            ->with('breed')
            ->get();

        $male_count = $livestocks->where('sex', 'M')->count();
        $female_count = $livestocks->where('sex', 'F')->count();

        return Inertia::render('livestocks/Index', [
            'livestocks' => $livestocks,
            'male_count' => $male_count,
            'female_count' => $female_count,
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
        $validated['farm_id'] = auth()->user()->current_farm_id;

        $livestock = new Livestock();
        $livestock->fill($validated);

        $photos = [];

        if (! is_null($request->photo) && ! empty($request->photo)) {
            foreach ($request->photo as $photo) {
                if (is_string($photo)) {
                    $photos[] = $photo;
                } else {
                    $path = $photo->store('livestocks', 'public');
                    $photos[] = $path;
                }
            }
        }

        $countLivestock = Livestock::query()->count();

        $livestock->photo = $photos;
        $livestock->aifarm_id = Livestock::generateAifarmId($countLivestock);
        $livestock->save();

        return redirect()->route('livestocks.show', $livestock);
    }

    /**
     * Display the specified resource.
     */
    public function show(Livestock $livestock)
    {
        $livestock->load('breed.species', 'maleParent', 'femaleParent');

        // Get weight history for the last 12 months
        $weightHistory = $livestock->weights()
            ->where('date', '>=', now()->subMonths(12))
            ->orderBy('date')
            ->get()
            ->groupBy(function($weight) {
                return \Carbon\Carbon::parse($weight->date)->format('Y-m');
            })
            ->map(function($monthWeights) {
                // Get the latest weight for each month
                return $monthWeights->sortByDesc('date')->first();
            })
            ->values();

        // Get milking history for the last 12 months
        $milkingHistory = $livestock->milkings()
            ->where('date', '>=', now()->subMonths(12))
            ->orderBy('date')
            ->get()
            ->groupBy(function($milking) {
                return \Carbon\Carbon::parse($milking->date)->format('Y-m');
            })
            ->map(function($monthMilkings) {
                // Sum all milk volumes for each month
                return [
                    'date' => $monthMilkings->first()->date,
                    'total_volume' => $monthMilkings->sum('milk_volume'),
                    'count' => $monthMilkings->count(),
                ];
            })
            ->values();

        return Inertia::render('livestocks/Show', [
            'livestock' => $livestock,
            'weightHistory' => $weightHistory,
            'milkingHistory' => $milkingHistory,
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

        $livestock->update(['weight' => $validated['weight']]);

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
        $validated = $request->validated();
        $livestock->fill($validated);

        $photos = $livestock->photo ?? [];

        if (! is_null($request->photo) && ! empty($request->photo)) {
            foreach ($request->photo as $photo) {
                if (is_string($photo)) {
                    $photos[] = $photo;
                } else {
                    $path = $photo->store('livestocks', 'public');
                    $photos[] = $path;
                }
            }
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
        $livestock->delete();
        return redirect()->route('livestocks.index');
    }

    public function search(Request $request)
    {
        $query = $request->input('q');
        $sex = $request->input('sex');

        $livestocks = Livestock::query()
            ->where('farm_id', Auth::user()->current_farm_id)
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
}
