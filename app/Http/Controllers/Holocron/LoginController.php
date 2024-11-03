<?php

namespace App\Http\Controllers\Holocron;

use App\Http\Controllers\Controller;

class LoginController extends Controller
{
    public function show()
    {
        if (auth()->check()) {
            return redirect()->route('holocron.dashboard');
        }

        return view('holocron.login');
    }

    public function create()
    {
        $credentials = request()->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (auth()->attempt($credentials)) {
            return redirect()->intended(route('holocron.dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }
}
