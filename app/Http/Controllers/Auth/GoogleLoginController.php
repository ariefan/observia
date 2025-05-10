<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\User;
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
        $googleUser = Socialite::driver('google')->user();
        $user = User::where('email', $googleUser->email)->first();
        if (! $user) {
            $user = User::create(['name' => $googleUser->name, 'email' => $googleUser->email, 'password' => Hash::make(rand(100000, 999999))]);
            $team = new Team();
            $team->user_id = $user->id;
            $team->personal_team = 1;
            $team->name = $googleUser->name.' Farm';
            $team->save();
            $user->teams()->attach($team);
        }

        Auth::login($user);

        return redirect()->route('dashboard');
    }
}
