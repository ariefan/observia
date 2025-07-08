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
            ->where('name', 'like', "%{$query}%")
            ->where('sex', $sex)
            ->with('breed')
            ->select('id', 'name', 'breed_id', 'aifarm_id', 'tag_id')
            ->get();

        return response()->json($livestocks);
    }
}
