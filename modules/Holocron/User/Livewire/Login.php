<?php

declare(strict_types=1);

namespace Modules\Holocron\User\Livewire;

use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Component;

class Login extends Component
{
    public string $email = '';

    public string $password = '';

    public function mount(): null
    {
        if (auth()->check()) {
            return $this->redirect(route('holocron.dashboard'));
        }

        return null;
    }

    public function render(): View
    {
        return view('holocron-user::login');
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
