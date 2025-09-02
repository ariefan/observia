<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\User;
use App\Models\FarmInvite;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class GoogleLoginController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        $googleUser = Socialite::driver('google')->stateless()->user();
        $user = User::where('email', $googleUser->email)->first();
        if (! $user) {
            $user = User::create(['name' => $googleUser->name, 'email' => $googleUser->email, 'password' => Hash::make(rand(100000, 999999))]);
            // $user->assignFarmRole('user');
            // Role::create(['name' => 'user', 'farm_id' => null]);
            // $team = new Team();
            // $team->user_id = $user->id;
            // $team->personal_team = 1;
            // $team->name = $googleUser->name.' Farm';
            // $team->save();
            // $user->teams()->attach($team);
        }


        Auth::login($user, true); // Always remember for Google login
        
        // Set custom session lifetime for Google login (14 days)
        $minutes = config('auth.remember_cookie_duration', 20160); // 14 days
        config(['session.lifetime' => $minutes]);
        
        // Note: Login event is automatically triggered by Auth::login(), no need to manually fire it

        return redirect()->route('home');
    }
}
