<?php

namespace App\Http\Controllers;

use App\Models\Livestock;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PublicLivestockController extends Controller
{
    public function show(Livestock $livestock)
    {
        // Load relationships
        $livestock->load([
            'breed.species',
            'herd',
            'farm.owner',
            'maleParent.breed',
            'femaleParent.breed',
            'maleParent.maleParent.breed',
            'maleParent.femaleParent.breed',
            'femaleParent.maleParent.breed',
            'femaleParent.femaleParent.breed'
        ]);

        // Get weight history (last 12 months grouped by month)
        $weightHistory = DB::table('livestock_weights')
            ->select(
                DB::raw("TO_CHAR(date, 'YYYY-MM') as month"),
                DB::raw('AVG(weight) as average_weight')
            )
            ->where('livestock_id', $livestock->id)
            ->where('date', '>=', Carbon::now()->subMonths(12))
            ->groupBy(DB::raw("TO_CHAR(date, 'YYYY-MM')"))
            ->orderBy('month')
            ->get()
            ->toArray();

        // Get milking history (last 12 months)
        $milkingHistory = DB::table('livestock_milkings')
            ->select(
                'date',
                DB::raw('SUM(milk_volume) as total_volume'),
                DB::raw('AVG(milk_volume) as average_volume'),
                'notes'
            )
            ->where('livestock_id', $livestock->id)
            ->where('date', '>=', Carbon::now()->subMonths(12))
            ->groupBy('date', 'notes')
            ->orderBy('date')
            ->get()
            ->toArray();

        // Calculate lactation days
        $lactationDays = 0;
        if ($livestock->sex == 'F' && !empty($milkingHistory)) {
            $firstMilkingDate = collect($milkingHistory)->min('date');
            $lastMilkingDate = collect($milkingHistory)->max('date');
            
            if ($firstMilkingDate && $lastMilkingDate) {
                $lactationDays = Carbon::parse($firstMilkingDate)->diffInDays(Carbon::parse($lastMilkingDate)) + 1;
            }
        }

        // Get pedigree data
        $pedigreeData = $this->buildPedigreeData($livestock);

        // Get health records
        $healthRecords = DB::table('livestock_health_records')
            ->where('livestock_id', $livestock->id)
            ->orderBy('record_date', 'desc')
            ->limit(10)
            ->get()
            ->toArray();

        // Get feeding history from herd feedings (if livestock has a herd)
        $feedingHistory = [];
        if ($livestock->herd_id) {
            $feedingHistory = DB::table('herd_feedings')
                ->join('rations', 'herd_feedings.ration_id', '=', 'rations.id')
                ->where('herd_feedings.herd_id', $livestock->herd_id)
                ->select('herd_feedings.*', 'rations.name as ration_name')
                ->orderBy('herd_feedings.date', 'desc')
                ->limit(10)
                ->get()
                ->toArray();
        }

        // Get latest ending status
        $latestEnding = DB::table('livestock_endings')
            ->where('livestock_id', $livestock->id)
            ->orderBy('ending_date', 'desc')
            ->first();

        return view('public.livestock.show', compact(
            'livestock',
            'weightHistory',
            'milkingHistory',
            'lactationDays',
            'pedigreeData',
            'healthRecords',
            'feedingHistory',
            'latestEnding'
        ));
    }

    private function buildPedigreeData(Livestock $livestock)
    {
        $pedigreeData = collect();

        // Add current livestock (depth 0)
        $pedigreeData->push((object)[
            'id' => $livestock->id,
            'name' => $livestock->name,
            'breed_name' => $livestock->breed->name ?? 'Unknown',
            'depth' => 0
        ]);

        // Add parents (depth 1)
        if ($livestock->maleParent) {
            $pedigreeData->push((object)[
                'id' => $livestock->maleParent->id,
                'name' => $livestock->maleParent->name,
                'breed_name' => $livestock->maleParent->breed->name ?? 'Unknown',
                'depth' => 1
            ]);
        }

        if ($livestock->femaleParent) {
            $pedigreeData->push((object)[
                'id' => $livestock->femaleParent->id,
                'name' => $livestock->femaleParent->name,
                'breed_name' => $livestock->femaleParent->breed->name ?? 'Unknown',
                'depth' => 1
            ]);
        }

        // Add grandparents (depth 2)
        if ($livestock->maleParent) {
            if ($livestock->maleParent->maleParent) {
                $pedigreeData->push((object)[
                    'id' => $livestock->maleParent->maleParent->id,
                    'name' => $livestock->maleParent->maleParent->name,
                    'breed_name' => $livestock->maleParent->maleParent->breed->name ?? 'Unknown',
                    'depth' => 2
                ]);
            }

            if ($livestock->maleParent->femaleParent) {
                $pedigreeData->push((object)[
                    'id' => $livestock->maleParent->femaleParent->id,
                    'name' => $livestock->maleParent->femaleParent->name,
                    'breed_name' => $livestock->maleParent->femaleParent->breed->name ?? 'Unknown',
                    'depth' => 2
                ]);
            }
        }

        if ($livestock->femaleParent) {
            if ($livestock->femaleParent->maleParent) {
                $pedigreeData->push((object)[
                    'id' => $livestock->femaleParent->maleParent->id,
                    'name' => $livestock->femaleParent->maleParent->name,
                    'breed_name' => $livestock->femaleParent->maleParent->breed->name ?? 'Unknown',
                    'depth' => 2
                ]);
            }

            if ($livestock->femaleParent->femaleParent) {
                $pedigreeData->push((object)[
                    'id' => $livestock->femaleParent->femaleParent->id,
                    'name' => $livestock->femaleParent->femaleParent->name,
                    'breed_name' => $livestock->femaleParent->femaleParent->breed->name ?? 'Unknown',
                    'depth' => 2
                ]);
            }
        }

        return $pedigreeData->toArray();
    }
}