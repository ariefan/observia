<?php

namespace App\Http\Controllers;

use App\Models\Breed;
use App\Models\Species;
use App\Http\Requests\StoreBreedRequest;
use App\Http\Requests\UpdateBreedRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BreedController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->is_super_user) {
                abort(403, 'Access denied. Super user privileges required.');
            }
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $search = $request->get('search');
        $speciesFilter = $request->get('species_id');
        
        $breeds = Breed::with(['species'])
            ->withCount('livestocks')
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('origin', 'like', "%{$search}%");
            })
            ->when($speciesFilter, function ($query, $speciesFilter) {
                $query->where('species_id', $speciesFilter);
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        $species = Species::orderBy('name')->get();

        return Inertia::render('Breeds/Index', [
            'breeds' => $breeds,
            'species' => $species,
            'search' => $search,
            'speciesFilter' => $speciesFilter,
        ]);
    }

    public function create()
    {
        $species = Species::orderBy('name')->get();

        return Inertia::render('Breeds/Create', [
            'species' => $species,
        ]);
    }

    public function store(StoreBreedRequest $request)
    {
        Breed::create($request->validated());

        return redirect()->route('breeds.index')
            ->with('success', 'Breed created successfully.');
    }

    public function show(Breed $breed)
    {
        $breed->load(['species', 'livestocks']);
        
        return Inertia::render('Breeds/Show', [
            'breed' => $breed,
        ]);
    }

    public function edit(Breed $breed)
    {
        $breed->load('species');
        $species = Species::orderBy('name')->get();

        return Inertia::render('Breeds/Edit', [
            'breed' => $breed,
            'species' => $species,
        ]);
    }

    public function update(UpdateBreedRequest $request, Breed $breed)
    {
        $breed->update($request->validated());

        return redirect()->route('breeds.index')
            ->with('success', 'Breed updated successfully.');
    }

    public function destroy(Breed $breed)
    {
        $livestockCount = $breed->livestocks()->count();

        if ($livestockCount > 0) {
            return redirect()->back()
                ->with('error', "Cannot delete breed. {$livestockCount} livestock records are using this breed.");
        }

        $breed->delete();

        return redirect()->route('breeds.index')
            ->with('success', 'Breed deleted successfully.');
    }
}
