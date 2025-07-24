<?php

namespace App\Http\Controllers;

use App\Models\Feed;
use App\Models\Ration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class RationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $rations = Ration::with('rationItems')->where('farm_id', auth()->user()->current_farm_id)->get();

        return Inertia::render('Rations/Index', [
            'rations' => $rations,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Rations/Form');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.feed' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $ration = Ration::create([
                'name' => $request->name,
                'farm_id' => Auth::user()->current_farm_id,
            ]);

            foreach ($request->items as $item) {
                $ration->rationItems()->create([
                    'feed' => $item['feed'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'farm_id' => Auth::user()->current_farm_id,
                ]);
            }
        });

        return redirect()->route('rations.index')->with('success', 'Ransum berhasil disimpan!');
    }


    /**
     * Display the specified resource.
     */
    public function show(Ration $ration)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Ration $ration)
    {
        if ($ration->farm_id !== Auth::user()->current_farm_id) {
            abort(403);
        }

        $ration->load('rationItems');

        return Inertia::render('Rations/Form', [
            'ration' => $ration,
        ]);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Ration $ration)
    {
        if ($ration->farm_id !== Auth::user()->current_farm_id) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.feed' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $ration) {
            $ration->update([
                'name' => $request->name,
            ]);

            // Nuking the old ration items for lazy update, just like our social life
            $ration->rationItems()->delete();

            foreach ($request->items as $item) {
                $ration->rationItems()->create([
                    'feed' => $item['feed'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'farm_id' => Auth::user()->current_farm_id,
                ]);
            }
        });

        return redirect()->route('rations.index')->with('success', 'Ransum berhasil diperbarui!');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ration $ration)
    {
        if ($ration->farm_id !== Auth::user()->current_farm_id) {
            abort(403);
        }
        $ration->delete();
        return redirect()->route('rations.index');
    }
}
