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
        $this->middleware('auth');
        $this->middleware('verified');
    }

    private function checkSuperUser()
    {
        if (!auth()->user()->is_super_user) {
            abort(403, 'Akses ditolak. Diperlukan hak akses super user.');
        }
    }

    public function index(Request $request)
    {
        $this->checkSuperUser();
        
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
        $this->checkSuperUser();
        
        return Inertia::render('Species/Create');
    }

    public function store(StoreSpeciesRequest $request)
    {
        $this->checkSuperUser();
        
        Species::create($request->validated());

        return redirect()->route('species.index')
            ->with('success', 'Spesies berhasil dibuat.');
    }

    public function show(Species $species)
    {
        $this->checkSuperUser();
        
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
        $this->checkSuperUser();
        
        return Inertia::render('Species/Edit', [
            'species' => $species,
        ]);
    }

    public function update(UpdateSpeciesRequest $request, Species $species)
    {
        $this->checkSuperUser();
        
        $species->update($request->validated());

        return redirect()->route('species.index')
            ->with('success', 'Spesies berhasil diperbarui.');
    }

    public function destroy(Species $species)
    {
        $this->checkSuperUser();
        
        $livestockCount = Livestock::whereHas('breed', function($query) use ($species) {
            $query->where('species_id', $species->id);
        })->count();

        if ($livestockCount > 0) {
            return redirect()->back()
                ->with('error', "Tidak dapat menghapus spesies. {$livestockCount} catatan ternak menggunakan spesies ini.");
        }

        $species->delete();

        return redirect()->route('species.index')
            ->with('success', 'Spesies berhasil dihapus.');
    }
}
