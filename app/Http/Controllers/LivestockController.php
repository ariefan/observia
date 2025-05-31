<?php

namespace App\Http\Controllers;

use App\Models\Livestock;
use App\Http\Requests\StoreLivestockRequest;
use App\Http\Requests\UpdateLivestockRequest;
use Inertia\Inertia;

class LivestockController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = [];
        return Inertia::render('livestocks/Index', $data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $data = [];
        return Inertia::render('livestocks/Form', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLivestockRequest $request)
    {
        Livestock::create($request->validated());
        return redirect()->route('livestocks.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(/*Livestock*/ $livestock)
    {
        $data = [];
        return Inertia::render('livestocks/Show', $data);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Livestock $livestock)
    {
        $data = [];
        return Inertia::render('livestocks/Form', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLivestockRequest $request, Livestock $livestock)
    {
        Livestock::update($request->validated());
        return redirect()->route('livestocks.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Livestock $livestock)
    {
        Livestock::destroy($livestock);
        return redirect()->route('livestocks.index');
    }
}
