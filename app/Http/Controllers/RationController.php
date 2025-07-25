<?php

namespace App\Http\Controllers;

use App\Models\Feed;
use App\Models\Ration;
use App\Models\HistoryRation;
use App\Models\HistoryRationItem;
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
        $month = request('month');
        $year = request('year');

        $rations = Ration::with('rationItems')
            ->where('farm_id', auth()->user()->current_farm_id)
            ->get();

        $historyRationsQuery = HistoryRation::with('historyRationItems')
            ->where('farm_id', auth()->user()->current_farm_id);

        if ($month) {
            $historyRationsQuery->whereMonth('created_at', $month);
        }
        if ($year) {
            $historyRationsQuery->whereYear('created_at', $year);
        }

        $historyRations = $historyRationsQuery
            ->orderBy('history_rations.created_at', 'desc')
            ->get();

        return Inertia::render('Rations/Index', [
            'rations' => $rations,
            'historyRations' => $historyRations,
            'selectedMonth' => $month,
            'selectedYear' => $year,
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
                ]);
            }

            // History
            $historyRation = HistoryRation::create([
                'action' => 'create',
                'ration_id' => $ration->id,
                'farm_id' => Auth::user()->current_farm_id,
                'name' => $ration->name,
            ]);

            foreach ($request->items as $item) {
                HistoryRationItem::create([
                    'history_ration_id' => $historyRation->id,
                    'ration_id' => $ration->id,
                    'feed' => $item['feed'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
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
    public function edit(Request $request, Ration $ration)
    {
        if ($ration->farm_id !== Auth::user()->current_farm_id) {
            abort(403);
        }

        $ration->load('rationItems');
        $restock = $request->query('restock', null) == "1" ? true : false;

        return Inertia::render('Rations/Form', [
            'ration' => $ration,
            'restock' => $restock,
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
            $restock = $request->restock == "1" ? true : false;

            $ration->update([
                'name' => $request->name,
            ]);

            // Update quantity and price for existing items, add new ones if needed
            $existingItems = $ration->rationItems()->get()->keyBy('feed');

            // History
            $historyRation = HistoryRation::create([
                'action' => $restock ? 'restock' : 'update',
                'ration_id' => $ration->id,
                'farm_id' => Auth::user()->current_farm_id,
                'name' => $ration->name,
            ]);

            foreach ($request->items as $item) {
                if ($existingItems->has($item['feed'])) {
                    // Update existing item
                    $existingItem = $existingItems->get($item['feed']);
                    $existingItem->update([
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                    ]);
                } else {
                    // Create new item
                    $ration->rationItems()->create([
                        'feed' => $item['feed'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                    ]);
                }
                
                // History
                HistoryRationItem::create([
                    'history_ration_id' => $historyRation->id,
                    'ration_id' => $ration->id,
                    'feed' => $item['feed'],
                    'quantity' => $restock ? $existingItem->quantity + $item['quantity'] : $item['quantity'],
                    'price' => $restock ? $existingItem->price + $item['price'] : $item['price'],
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
