<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Farm;
use App\Models\Province;
use App\Models\City;
use App\Http\Requests\StoreFarmRequest;
use App\Http\Requests\UpdateFarmRequest;
use Inertia\Inertia;

class FarmController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // $user = Auth::user();
        // $farm = Farm::where('id', $user->current_farm_id)->first();

        return Inertia::render('farms/Form', [
            'provinces' => Province::all(),
            'cities' => City::all(),
            'user' => $user ?? null,
            'farm' => $farm ?? null,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFarmRequest $request)
    {
        $data = $request->validated();
        $user = auth()->user();

        $data['picture'] = null;
        $farm = Farm::create([
            ...$data,
            'user_id' => auth()->id(),
        ]);

        $user->update(['current_farm_id' => $farm->id]);
        $user->farms()->attach($farm->id);
        $user->assignFarmRole('owner');

        return redirect()->route('farms.show', ['farm' => $farm->id])
            ->with('success', 'Farm created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Farm $farm)
    {

        return Inertia::render('farms/Show', [
            'farm' => $farm ?? null,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Farm $farm)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFarmRequest $request, Farm $farm)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Farm $farm)
    {
        //
    }
}
