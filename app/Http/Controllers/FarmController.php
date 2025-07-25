<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Farm;
use App\Models\FarmInvite;
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
            $data['picture'] = asset('storage/' . $path);
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
        $farm = Farm::findOrFail(auth()->user()->current_farm_id);
        $farm->load(['users']);
        return Inertia::render('farms/Show', [
            'farm' => $farm ?? null,
            'invites' => FarmInvite::where('farm_id', $farm->id)->get(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Farm $farm)
    {
        $farm = Farm::findOrFail(auth()->user()->current_farm_id);
        $farm->load(['city.province']);
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
        $data = $request->validated();

        // Check if a new picture was uploaded
        if ($request->hasFile('picture_blob')) {
            $file = $request->file('picture_blob');
            $path = $file->store('farm_pictures', 'public');
            $data['picture'] = asset('storage/' . $path);

            // Optional: delete the old picture from storage if it exists
            if ($farm->picture && \Storage::disk('public')->exists($farm->picture)) {
                \Storage::disk('public')->delete($farm->picture);
            }
        }

        $farm->update($data);
        return redirect()->back()->with('success', 'Data peternakan berhasil diperbarui.');
    }
    
    public function updateRole(Request $request, Farm $farm, User $user)
    {
        $validated = $request->validate([
            'role' => 'required|string|max:50',
        ]);

        // Check if the user is actually related to this farm
        if (!$farm->users()->where('user_id', $user->id)->exists()) {
            return back()->with('error', 'Anda tidak memiliki akses ke peternakan ini.');
        }

        // Update the pivot table
        $farm->users()->updateExistingPivot($user->id, ['role' => $validated['role']]);

        return back()->with('success', 'Role updated!');
    }
    
    public function updateRoleInvite(Request $request, Farm $farm, $email)
    {
        $validated = $request->validate([
            'email' => 'required|email|exists:farm_invites,email,farm_id,' . $farm->id,
            'role' => 'required|string|max:50',
        ]);

        FarmInvite::where('farm_id', $farm->id)
            ->where('email', $email)
            ->update(['role' => $validated['role']]);

        return back()->with('success', 'Role updated!');
    }
    
    public function switch(Request $request, Farm $farm)
    {
        $user = auth()->user();

        // Check if the farm belongs to the user
        if (!$user->farms()->where('farms.id', $farm->id)->exists()) {
            return redirect()->route('farms.index')->with('error', 'Anda tidak memiliki akses ke peternakan ini.');
        }

        // Update the user's current farm
        $user->update(['current_farm_id' => $farm->id]);

        $currentRoute = $request->route()->getName();

       return redirect()->route('dashboard')->with('success', 'Anda telah beralih ke peternakan: ' . $farm->name);
    }
    
    public function inviteMember(Request $request, Farm $farm)
    {
        $request->validate([
            'email' => 'required|email|unique:farm_invites,email,NULL,id,farm_id,' . $farm->id,
            'role' => 'required|string|max:50',
        ]);

        FarmInvite::create([
            'farm_id' => $farm->id,
            'email' => $request->email,
            'role' => $request->role,
        ]);
       return redirect()->back()->with('success', 'Pengguna dengan email ' . $request->email . ' telah diundang ke peternakan: ' . $farm->name);
    }
    
    public function destroyMember(Request $request, Farm $farm, User $user)
    {
        // Check if the user is actually related to this farm
        if (!$farm->users()->where('user_id', $user->id)->exists()) {
            return back()->with('error', 'Anda tidak memiliki akses ke peternakan ini.');
        }

        // Remove the user from the farm
        $farm->users()->detach($user->id);
        $user->update(['current_farm_id' => null]);

        return back()->with('success', 'Pengguna dengan ID ' . $user->id . ' telah dihapus dari peternakan: ' . $farm->name);
    }
    
    public function destroyMemberInvite(Request $request, Farm $farm, string $email)
    {
        // Check if the invite exists
        $invite = FarmInvite::where('farm_id', $farm->id)
            ->where('email', $email)
            ->first();

        if (!$invite) {
            return back()->with('error', 'Undangan tidak ditemukan untuk email: ' . $email);
        }

        // Delete the invite
        $invite->delete();

        return back()->with('success', 'Undangan untuk email ' . $email . ' telah dihapus dari peternakan: ' . $farm->name);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Farm $farm)
    {
        //
    }
}
