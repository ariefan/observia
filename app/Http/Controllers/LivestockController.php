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
                $path = $photo->store('livestocks', 'public');
                $photos[] = $path;
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

        return Inertia::render('livestocks/Show', [
            'livestock' => $livestock,
        ]);
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
                $path = $photo->store('livestocks', 'public');
                $photos[] = $path;
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
        Livestock::destroy($livestock);
        return redirect()->route('livestocks.index');
    }
}
