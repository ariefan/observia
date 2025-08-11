<?php

namespace App\Http\Controllers;

use App\Models\Feed;
use App\Models\Ration;
use App\Models\HistoryRation;
use App\Models\HistoryRationItem;
use App\Models\HerdFeeding;
use App\Models\FeedingLeftover;
use App\Models\Herd;
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

        // Get herd feedings for the current farm
        $herdFeedingsQuery = HerdFeeding::with(['herd.livestocks', 'ration'])
            ->whereHas('herd', function ($query) {
                $query->where('farm_id', auth()->user()->current_farm_id);
            });

        if ($month) {
            $herdFeedingsQuery->whereMonth('date', $month);
        }
        if ($year) {
            $herdFeedingsQuery->whereYear('date', $year);
        }

        $herdFeedings = $herdFeedingsQuery
            ->orderBy('date', 'desc')
            ->orderBy('time', 'desc')
            ->get()
            ->map(function ($feeding) {
                $feeding->livestock_count = $feeding->herd && $feeding->herd->livestocks ? $feeding->herd->livestocks->count() : 1;
                return $feeding;
            });

        // Get feeding leftovers for the current farm
        $feedingLeftoversQuery = FeedingLeftover::with(['feeding.herd', 'feeding.ration'])
            ->whereHas('feeding.herd', function ($query) {
                $query->where('farm_id', auth()->user()->current_farm_id);
            });

        if ($month) {
            $feedingLeftoversQuery->whereMonth('date', $month);
        }
        if ($year) {
            $feedingLeftoversQuery->whereYear('date', $year);
        }

        $feedingLeftovers = $feedingLeftoversQuery
            ->orderBy('date', 'desc')
            ->orderBy('time', 'desc')
            ->get();

        return Inertia::render('Rations/Index', [
            'rations' => $rations,
            'historyRations' => $historyRations,
            'herdFeedings' => $herdFeedings,
            'feedingLeftovers' => $feedingLeftovers,
            'selectedMonth' => $month,
            'selectedYear' => $year,
            'tab' => request('tab'),
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
                        'quantity' => $restock ? $existingItem->quantity + $item['quantity'] : $item['quantity'],
                        'price' => $restock ? $existingItem->price + $item['price'] : $item['price'],
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
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
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

    public function search(Request $request)
    {
        $query = $request->input('q');
        $id = $request->input('id');

        $rations = Ration::query()
            ->where('farm_id', auth()->user()->current_farm_id)
            ->when($id, fn ($q) => $q->where('id', $id))
            ->when(!$id && $query, function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%");
            })
            ->with('rationItems')
            ->select('id', 'name')
            ->limit(10)
            ->get()
            ->map(function ($ration) {
                $ration->total_quantity = $ration->rationItems->sum('quantity');
                return $ration;
            });

        return response()->json($rations);
    }

    /**
     * Show the form for recording leftover feed
     */
    public function leftover()
    {
        // Get feedings without leftover records from last 7 days
        $availableFeedings = HerdFeeding::with(['herd.livestocks', 'ration'])
            ->whereHas('herd', function ($query) {
                $query->where('farm_id', auth()->user()->current_farm_id);
            })
            ->whereDoesntHave('leftover')
            ->where('date', '>=', now()->subDays(7))
            ->orderBy('date', 'desc')
            ->orderBy('time', 'desc')
            ->get()
            ->map(function ($feeding) {
                $feeding->livestock_count = $feeding->herd && $feeding->herd->livestocks ? $feeding->herd->livestocks->count() : 1;
                return $feeding;
            });

        return Inertia::render('Rations/LeftoverForm', [
            'availableFeedings' => $availableFeedings,
        ]);
    }

    /**
     * Store leftover feed record
     */
    public function storeLeftover(Request $request)
    {
        $validated = $request->validate([
            'feeding_id' => 'required|exists:herd_feedings,id',
            'leftover_quantity' => 'required|numeric|min:0',
            'date' => 'required|date',
            'time' => 'nullable|date_format:H:i',
            'notes' => 'nullable|string|max:1000',
            'month' => 'nullable|string',
            'year' => 'nullable|string',
            'tab' => 'nullable|string',
        ]);

        // Get the feeding to validate leftover quantity
        $feeding = HerdFeeding::find($validated['feeding_id']);
        
        if ($validated['leftover_quantity'] > $feeding->quantity) {
            return back()->withErrors([
                'leftover_quantity' => 'Sisa pakan tidak boleh melebihi jumlah pakan yang diberikan (' . $feeding->quantity . ' kg).'
            ]);
        }

        // Check if leftover already exists for this feeding
        $existingLeftover = FeedingLeftover::where('feeding_id', $validated['feeding_id'])->first();
        
        if ($existingLeftover) {
            // Update existing leftover
            $existingLeftover->update([
                'leftover_quantity' => $validated['leftover_quantity'],
                'date' => $validated['date'],
                'time' => $validated['time'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'user_id' => auth()->id(),
            ]);
            
            return redirect()->route('rations.index', [
                'month' => $validated['month'] ?? $request->get('month'),
                'year' => $validated['year'] ?? $request->get('year'),
                'tab' => $validated['tab'] ?? $request->get('tab', 'feed')
            ])->with('success', 'Sisa pakan berhasil diperbarui!');
        } else {
            // Create new leftover record
            FeedingLeftover::create([
                'feeding_id' => $validated['feeding_id'],
                'leftover_quantity' => $validated['leftover_quantity'],
                'date' => $validated['date'],
                'time' => $validated['time'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'user_id' => auth()->id(),
            ]);
            
            return redirect()->route('rations.index', [
                'month' => $validated['month'] ?? $request->get('month'),
                'year' => $validated['year'] ?? $request->get('year'),
                'tab' => $validated['tab'] ?? $request->get('tab', 'feed')
            ])->with('success', 'Sisa pakan berhasil dicatat!');
        }
    }
}
