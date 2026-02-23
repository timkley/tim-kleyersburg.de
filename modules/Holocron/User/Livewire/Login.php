<?php

declare(strict_types=1);

namespace Modules\Holocron\User\Livewire;

use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Component;
use Modules\Holocron\User\Models\User;

class Login extends Component
{
    public string $email = '';

    public string $password = '';

    public function mount(): null
    {
        if (auth()->check()) {
            return $this->redirect(route('holocron.dashboard'));
        }

        if ($this->shouldAutoLogin() && $this->autoLoginUser()) {
            $intendedUrl = session()->pull('url.intended');
            $redirectUrl = is_string($intendedUrl) && $intendedUrl !== ''
                ? $intendedUrl
                : route('holocron.dashboard');

            return $this->redirect($redirectUrl);
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

    protected function shouldAutoLogin(): bool
    {
        return (bool) config('auth.local_auto_login.enabled');
    }

    protected function autoLoginUser(): bool
    {
        $email = config('auth.local_auto_login.email');

        if (! is_string($email) || mb_trim($email) === '') {
            return false;
        }

        $user = User::query()
            ->where('email', $email)
            ->first();

        if (! $user) {
            return false;
        }

        auth()->login($user, remember: true);
        session()->regenerate();

        return true;
    }
}
