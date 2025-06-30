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
        if(auth()->user()->current_farm_id) {
            return redirect()->route('dashboard');
        }
        $data = [];
        return Inertia::render('home/Home', $data);
    }
    
    public function farmLogout(Request $request)
    {
        auth()->user()->update(['current_farm_id' => null]);
        return redirect()->route('home')->with('success', 'Anda telah logout ke peternakan. Pilih peternakan untuk login kembali.');
    }

    public function dashboard()
    {
        $data = [];
        return Inertia::render('home/Dashboard', $data);
    }
}
