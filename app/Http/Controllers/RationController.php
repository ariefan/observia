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
        $rations = Ration::where('farm_id', auth()->user()->current_farm_id)->with('rationItems.feed')->get();

        return Inertia::render('Rations/Index', [
            'rations' => $rations,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $feeds = Feed::where('farm_id', auth()->user()->current_farm_id)->get();
        return Inertia::render('Rations/Form', [
            'feeds' => $feeds,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.feed_id' => 'required|exists:feeds,id',
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
                    'feed_id' => $item['feed_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'farm_id' => Auth::user()->current_farm_id,
                ]);
            }
        });

        return redirect()->route('rations.index');
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
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Ration $ration)
    {
        //
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
