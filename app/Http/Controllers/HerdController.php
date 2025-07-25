<?php

namespace App\Http\Controllers;

use App\Models\Herd;
use App\Models\Ration;
use App\Http\Requests\StoreHerdRequest;
use App\Http\Requests\UpdateHerdRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HerdController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $herds = Herd::where('farm_id', auth()->user()->current_farm_id)
            ->withCount('livestocks')
            ->get()
            ->map(function ($herd) {
                $herd->current_capacity = $herd->livestocks_count;
                return $herd;
            });
        return inertia('herds/Index', [
            'herds' => $herds,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return inertia('herds/Form');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreHerdRequest $request)
    {
        $data = $request->validated();
        $data['farm_id'] = auth()->user()->current_farm_id;
        $herd = Herd::create($data);
        return redirect()->route('herds.index')->with('success', 'Kandang berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Herd $herd)
    {
        $herd->load('livestocks');
        return inertia('herds/Show', [
            'herd' => $herd,
            'livestocks' => $herd->livestocks,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Herd $herd)
    {
        return inertia('herds/Form', [
            'herd' => $herd,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateHerdRequest $request, Herd $herd)
    {
        $herd->update($request->validated());
        return redirect()->route('herds.index')->with('success', 'Kandang berhasil diupdate.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Herd $herd)
    {
        $herd->delete();
        return redirect()->route('herds.index')->with('success', 'Kandang berhasil dihapus.');
    }

    public function search(Request $request)
    {
        $query = $request->input('q');
        $id = $request->input('id');

        $herds = Herd::query()
            ->where('farm_id', auth()->user()->current_farm_id)
            ->when($id, fn ($q) => $q->where('id', $id))
            ->when(!$id && $query, function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%");
            })
            ->select('id', 'name', 'capacity')
            ->limit(10)
            ->get();

        return response()->json($herds);
    }    
    
    public function feeding()
    {
        return Inertia::render('herds/Feeding', [
            'herd' => new Herd(),
            'ration' => new Ration(),
        ]);
    }

    public function storeFeeding(Request $request)
    {
        $validated = $request->validate([
            'herd_id' => 'required|exists:herds,id',
            'quantity' => 'required|numeric|min:0',
            'date' => 'required|date',
            'time' => 'nullable|date_format:H:i',
            'session' => 'required|in:morning,afternoon',
            'notes' => 'nullable|string|max:1000',
        ]);

        $herd = Herd::find($validated['herd_id']);

        // Check if feeding already exists for this herd, date, and session
        $existingFeeding = $herd->feedings()
            ->where('date', $validated['date'])
            ->where('session', $validated['session'])
            ->first();

        if ($existingFeeding) {
            return back()->withErrors([
                'session' => 'Data pakan untuk sesi ' . $validated['session'] . ' pada tanggal ini sudah ada. Setiap sesi hanya boleh dilakukan sekali per hari.'
            ]);
        }

        $herd->feedings()->create([
            'quantity' => $validated['quantity'],
            'date' => $validated['date'],
            'time' => $validated['time'] ?? null,
            'session' => $validated['session'],
            'notes' => $validated['notes'] ?? null,
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('herds.show', $herd);
    }
}
