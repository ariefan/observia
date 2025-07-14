<?php

namespace App\Http\Controllers;

use App\Models\Herd;
use App\Http\Requests\StoreHerdRequest;
use App\Http\Requests\UpdateHerdRequest;
use Illuminate\Http\Request;

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
        $herd = Herd::create($request->validated());
        return redirect()->route('herds.index')->with('success', 'Kandang berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Herd $herd)
    {
        return inertia('herds/Form', [
            'herd' => $herd,
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
}
