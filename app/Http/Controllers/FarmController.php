<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Farm;
use App\Models\Province;
use App\Models\City;
use App\Http\Requests\StoreFarmRequest;
use App\Http\Requests\UpdateFarmRequest;
use Illuminate\Http\Request;
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

        if ($request->hasFile('picture_blob')) {
            $file = $request->file('picture_blob');
            $path = $file->store('farm_pictures', 'public');
            $data['picture'] = $path;
        }
        $farm = Farm::create([
            ...$data,
            'user_id' => auth()->id(),
        ]);

        $user->update(['current_farm_id' => $farm->id]);
        $user->farms()->attach($farm->id, ['role' => 'owner']);
        // $user->assignFarmRole('owner');

        return redirect()->route('farms.show', ['farm' => $farm->id])
            ->with('success', 'Farm created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Farm $farm)
    {
        $farm->load(['users']);
        return Inertia::render('farms/Show', [
            'farm' => $farm ?? null,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Farm $farm)
    {
        return Inertia::render('farms/Form', [
            'provinces' => Province::all(),
            'cities' => City::all(),
            'user' => $user ?? null,
            'farm' => $farm ?? null,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFarmRequest $request, Farm $farm)
    {
        //
    }
    
    public function updateRole(Request $request, Farm $farm, User $user)
    {
        $validated = $request->validate([
            'role' => 'required|string|max:50',
        ]);

        // Check if the user is actually related to this farm
        if (!$farm->users()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'User not part of this farm'], 404);
        }

        // Update the pivot table
        $farm->users()->updateExistingPivot($user->id, ['role' => $validated['role']]);

        return back()->with('success', 'Role updated!');
    }
    
    public function destroyMember(Request $request)
    {
        //
    }
    
    public function inviteMember(Request $request)
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
