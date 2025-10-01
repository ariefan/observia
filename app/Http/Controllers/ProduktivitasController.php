<?php

namespace App\Http\Controllers;

use App\Models\Livestock;
use App\Models\LivestockMilking;
use App\Models\LivestockWeight;
use App\Traits\HasCurrentFarm;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ProduktivitasController extends Controller
{
    use HasCurrentFarm;

    public function index()
    {
        return Inertia::render('Produktivitas/Index');
    }

    public function susu(Request $request)
    {
        $farm = $this->getCurrentFarm();
        $date = $request->get('date', Carbon::today()->toDateString());
        
        // Get milk production rankings prioritizing recent dates and higher volumes
        $rankings = LivestockMilking::whereHas('livestock', function ($query) use ($farm) {
                $query->where('farm_id', $farm->id);
            })
            ->with([
                'livestock' => function ($query) {
                    $query->with([
                        'herd:id,name',
                        'farm:id,name',
                    ]);
                }
            ])
            ->orderBy('date', 'desc')
            ->orderBy('milk_volume', 'desc')
            ->get()
            ->groupBy('livestock_id')
            ->map(function ($milkings) {
                $livestock = $milkings->first()->livestock;
                $latestMilking = $milkings->sortByDesc('date')->first();
                
                // Get total production for the most recent date
                $latestDate = $latestMilking->date;
                $dailyProduction = $milkings
                    ->where('date', $latestDate)
                    ->sum('milk_volume');
                
                // Get avatar URL from storage
                $avatarUrl = null;
                if ($livestock->photo) {
                    $photoPath = is_array($livestock->photo) ? ($livestock->photo[0] ?? null) : $livestock->photo;
                    if ($photoPath) {
                        $avatarUrl = Storage::url($photoPath);
                    }
                }
                
                return [
                    'id' => $livestock->id,
                    'name' => $livestock->name,
                    'tag_id' => $livestock->tag_id,
                    'daily_milk_production' => $dailyProduction,
                    'latest_date' => $latestDate,
                    'record_date' => $latestMilking->date,
                    'avatar' => $avatarUrl,
                    'herd_name' => $livestock->herd?->name,
                ];
            })
            ->sortByDesc(function ($item) {
                // Sort by volume first, then by date
                return str_pad($item['daily_milk_production'], 10, '0', STR_PAD_LEFT) . '_' . $item['latest_date'];
            })
            ->values()
            ->map(function ($item, $index) {
                $item['rank'] = $index + 1;
                return $item;
            });

        return Inertia::render('Produktivitas/Susu', [
            'rankings' => $rankings,
            'date' => Carbon::parse($date)->format('d F Y'),
        ]);
    }

    public function bobot(Request $request)
    {
        $farm = $this->getCurrentFarm();
        $date = $request->get('date', Carbon::today()->toDateString());
        
        // Get weight rankings prioritizing recent dates and higher weights
        $rankings = LivestockWeight::whereHas('livestock', function ($query) use ($farm) {
                $query->where('farm_id', $farm->id);
            })
            ->with([
                'livestock' => function ($query) {
                    $query->with([
                        'herd:id,name',
                        'farm:id,name',
                    ]);
                }
            ])
            ->orderBy('date', 'desc')
            ->orderBy('weight', 'desc')
            ->get()
            ->groupBy('livestock_id')
            ->map(function ($weights) {
                $livestock = $weights->first()->livestock;
                $latestWeight = $weights->sortByDesc('date')->first();
                
                // Get avatar URL from storage
                $avatarUrl = null;
                if ($livestock->photo) {
                    $photoPath = is_array($livestock->photo) ? ($livestock->photo[0] ?? null) : $livestock->photo;
                    if ($photoPath) {
                        $avatarUrl = Storage::url($photoPath);
                    }
                }
                
                return [
                    'id' => $livestock->id,
                    'name' => $livestock->name,
                    'tag_id' => $livestock->tag_id,
                    'current_weight' => $latestWeight->weight,
                    'latest_date' => $latestWeight->date,
                    'record_date' => $latestWeight->date,
                    'avatar' => $avatarUrl,
                    'herd_name' => $livestock->herd?->name,
                    'weight_unit' => 'kg',
                    'farm' => $livestock->farm ? [
                        'name' => $livestock->farm->name,
                    ] : null,
                ];
            })
            ->sortByDesc(function ($item) {
                // Sort by weight first, then by date
                return str_pad($item['current_weight'], 10, '0', STR_PAD_LEFT) . '_' . $item['latest_date'];
            })
            ->values()
            ->map(function ($item, $index) {
                $item['rank'] = $index + 1;
                return $item;
            });

        return Inertia::render('Produktivitas/Bobot', [
            'rankings' => $rankings,
            'date' => Carbon::parse($date)->format('d F Y'),
        ]);
    }
}
