<?php

declare(strict_types=1);

namespace App\Livewire\Holocron;

use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Component;

class Login extends Component
{
    public string $email = '';

    public string $password = '';

    public function mount()
    {
        if (auth()->check()) {
            return $this->redirect(route('holocron.dashboard'));
        }
    }

    public function render(): View
    {
        return view('holocron.login');
    }

    public function login(): null
    {
        $credentials = $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (auth()->attempt($credentials, remember: true)) {
            return $this->redirect(route('holocron.dashboard'));
        }

        throw ValidationException::withMessages([
            'email' => trans('auth.failed'),
        ]);
    }
}
