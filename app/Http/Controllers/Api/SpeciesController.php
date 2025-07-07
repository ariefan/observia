<?php

namespace App\Http\Controllers\Api;

use App\Models\Species;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SpeciesController extends Controller
{
    public function breeds(Species $species)
    {
        return response()->json($species->breeds);
    }
}
