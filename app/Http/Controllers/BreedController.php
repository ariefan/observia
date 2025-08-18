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
        $this->checkSuperUser();
        
        $species = Species::orderBy('name')->get();

        return Inertia::render('Breeds/Create', [
            'species' => $species,
        ]);
    }

    public function store(StoreBreedRequest $request)
    {
        $this->checkSuperUser();
        
        Breed::create($request->validated());

        return redirect()->route('breeds.index')
            ->with('success', 'Ras berhasil dibuat.');
    }

    public function show(Breed $breed)
    {
        $this->checkSuperUser();
        
        $breed->load(['species', 'livestocks']);
        
        return Inertia::render('Breeds/Show', [
            'breed' => $breed,
        ]);
    }

    public function edit(Breed $breed)
    {
        $this->checkSuperUser();
        
        $breed->load('species');
        $species = Species::orderBy('name')->get();

        return Inertia::render('Breeds/Edit', [
            'breed' => $breed,
            'species' => $species,
        ]);
    }

    public function update(UpdateBreedRequest $request, Breed $breed)
    {
        $this->checkSuperUser();
        
        $breed->update($request->validated());

        return redirect()->route('breeds.index')
            ->with('success', 'Ras berhasil diperbarui.');
    }

    public function destroy(Breed $breed)
    {
        $this->checkSuperUser();
        
        $livestockCount = $breed->livestocks()->count();

        if ($livestockCount > 0) {
            return redirect()->back()
                ->with('error', "Tidak dapat menghapus ras. {$livestockCount} catatan ternak menggunakan ras ini.");
        }

        $breed->delete();

        return redirect()->route('breeds.index')
            ->with('success', 'Ras berhasil dihapus.');
    }
}
