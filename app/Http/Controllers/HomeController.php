<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Farm;

class HomeController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // dd(auth()->user()->farms);
        $data = [];
        return Inertia::render('home/Home', $data);
    }

    public function dashboard()
    {
        $data = [];
        return Inertia::render('home/Dashboard', $data);
    }
}
