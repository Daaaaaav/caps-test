<?php

namespace App\Livewire\Pages\Auth;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use App\Services\CaptchaService;

#[Layout('layouts.auth')]
#[Title('Login')]
class Login extends Component
{
    public string $email = '';
    public string $password = '';
    public bool $remember = false;
    public string $captcha = '';

    public function mount()
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }
    }

    protected function rules(): array
    {
        return [
            'email'    => ['required', 'email'],
            'password' => ['required', 'string', 'min:6'],
            'captcha'  => ['required'],
        ];
    }

    public function login()
    {
        \Log::info('LOGIN: Attempt started', ['email' => $this->email]);

        // Validate input
        $this->validate();

        // Rate limiting
        $key = 'login:' . Str::lower($this->email) . '|' . request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'email' => "Too many login attempts. Please try again in {$seconds} seconds.",
            ]);
        }

        // Verify captcha
        if (!CaptchaService::verify($this->captcha, request()->ip())) {
            \Log::warning('LOGIN: Captcha failed', ['email' => $this->email]);
            $this->dispatch('captcha-error');
            $this->captcha = '';
            throw ValidationException::withMessages([
                'captcha' => 'Captcha verification failed. Please try again.',
            ]);
        }

        // Attempt authentication
        if (!Auth::attempt(['email' => Str::lower($this->email), 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($key, 60);
            $this->dispatch('captcha-error');
            $this->captcha = '';
            \Log::warning('LOGIN: Invalid credentials', ['email' => $this->email]);
            throw ValidationException::withMessages([
                'email' => 'These credentials do not match our records.',
            ]);
        }

        // Success
        RateLimiter::clear($key);
        request()->session()->regenerate();
        
        \Log::info('LOGIN: Success', ['email' => $this->email, 'user_id' => Auth::id()]);

        return redirect()->intended(route('home'));
    }

    public function render()
    {
        return view('livewire.pages.auth.login');
    }
}
