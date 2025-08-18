<?php

namespace App\Http\Controllers;

use App\Models\Species;
use App\Models\Livestock;
use App\Http\Requests\StoreSpeciesRequest;
use App\Http\Requests\UpdateSpeciesRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SpeciesController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->is_super_user) {
                abort(403, 'Access denied. Super admin privileges required.');
            }
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $search = $request->get('search');
        
        $species = Species::withCount(['breeds', 'livestocks' => function($query) {
            $query->whereHas('breed.species');
        }])
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('binomial_nomenclature', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Species/Index', [
            'species' => $species,
            'search' => $search,
        ]);
    }

    public function create()
    {
        return Inertia::render('Species/Create');
    }

    public function store(StoreSpeciesRequest $request)
    {
        Species::create($request->validated());

        return redirect()->route('species.index')
            ->with('success', 'Species created successfully.');
    }

    public function show(Species $species)
    {
        $species->load(['breeds' => function($query) {
            $query->withCount('livestocks');
        }]);
        
        $livestockCount = Livestock::whereHas('breed', function($query) use ($species) {
            $query->where('species_id', $species->id);
        })->count();

        return Inertia::render('Species/Show', [
            'species' => $species,
            'livestockCount' => $livestockCount,
        ]);
    }

    public function edit(Species $species)
    {
        return Inertia::render('Species/Edit', [
            'species' => $species,
        ]);
    }

    public function update(UpdateSpeciesRequest $request, Species $species)
    {
        $species->update($request->validated());

        return redirect()->route('species.index')
            ->with('success', 'Species updated successfully.');
    }

    public function destroy(Species $species)
    {
        $livestockCount = Livestock::whereHas('breed', function($query) use ($species) {
            $query->where('species_id', $species->id);
        })->count();

        if ($livestockCount > 0) {
            return redirect()->back()
                ->with('error', "Cannot delete species. {$livestockCount} livestock records are using this species.");
        }

        $species->delete();

        return redirect()->route('species.index')
            ->with('success', 'Species deleted successfully.');
    }
}
