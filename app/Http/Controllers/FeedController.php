<?php

namespace App\Http\Controllers;

use App\Models\Feed;
use App\Traits\HasCurrentFarm;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FeedController extends Controller
{
    use HasCurrentFarm;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $feeds = Feed::where('farm_id', $this->getCurrentFarmId())->get();

        return Inertia::render('Feeds/Index', [
            'feeds' => $feeds,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Feeds/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        Feed::create([
            'name' => $request->name,
            'farm_id' => $this->getCurrentFarmId(),
        ]);

        return redirect()->route('feeds.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Feed $feed)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Feed $feed)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Feed $feed)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Feed $feed)
    {
        $feed->delete();
        return redirect()->route('feeds.index');
    }
}
