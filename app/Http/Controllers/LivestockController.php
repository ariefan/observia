<?php

namespace App\Http\Controllers;

use App\Models\Livestock;
use App\Models\Species;
use App\Models\Breed;
use App\Http\Requests\StoreLivestockRequest;
use App\Http\Requests\UpdateLivestockRequest;
use Inertia\Inertia;

class LivestockController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = [];
        return Inertia::render('livestocks/Index', $data);
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
        $livestock = new Livestock();
        $livestock->fill($request->validated());
        $livestock->team_id = auth()->user()->current_team_id;
        $photos = [];

        if (! is_null($request->photo) && ! empty($request->photo)) {
            foreach ($request->photo as $key => $photo) {
                if ($key > 4) {
                    break;
                }
                $extension = $photo->extension();
                $filename = Str::uuid().".{$extension}";
                $photo->storeAs('livestock-photos', $filename);
                $photos[] = asset("storage/livestock-photos/{$filename}");
            }
        }

        $countLivestock = Livestock::query()->count();

        $livestock->photo = $photos;
        $livestock->aifarm_id = Livestock::generateAifarmId($countLivestock);
        $livestock->save();

        return redirect()
            ->route('livestocks.index')
            ->with('message', 'Livestock Created Successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(/*Livestock*/ $livestock)
    {
        $data = [];
        return Inertia::render('livestocks/Show', $data);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Livestock $livestock)
    {
        $data = [];
        return Inertia::render('livestocks/Form', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLivestockRequest $request, Livestock $livestock)
    {
        Livestock::update($request->validated());
        return redirect()->route('livestocks.index');
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
