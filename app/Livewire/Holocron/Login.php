<?php

namespace App\Livewire\Holocron;

use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Login extends Component
{
    public $email = '';

    public $password = '';

    public function mount()
    {
        if (auth()->check()) {
            return $this->redirect(route('holocron.dashboard'));
        }
    }

    public function render()
    {
        return view('holocron.login');
    }

    public function login()
    {
        $credentials = $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (auth()->attempt($credentials)) {
            return $this->redirect(route('holocron.dashboard'));
        }

        throw ValidationException::withMessages([
            'email' => trans('auth.failed'),
        ]);
    }
}
