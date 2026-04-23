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
use App\Services\OtpService;
use App\Models\User;

#[Layout('layouts.auth')]
#[Title('Login')]
class Login extends Component
{
    public string $email = '';
    public string $password = '';
    public bool $remember = false;
    public string $captcha = '';
    
    // OTP fields
    public string $otpCode = '';
    public bool $otpSent = false;
    public bool $showOtpInput = false;
    public int $otpExpiresIn = 0;

    public function mount()
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }
    }

    protected function rules(): array
    {
        if ($this->showOtpInput) {
            return [
                'otpCode' => ['required', 'string', 'size:6'],
            ];
        }

        return [
            'email'    => ['required', 'email'],
            'password' => ['required', 'string', 'min:6'],
            // 'captcha'  => ['required'], // COMMENTED OUT FOR DEVELOPMENT
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

        // Verify captcha - COMMENTED OUT FOR DEVELOPMENT
        // if (!CaptchaService::verify($this->captcha, request()->ip())) {
        //     \Log::warning('LOGIN: Captcha failed', ['email' => $this->email]);
        //     $this->dispatch('captcha-error');
        //     $this->captcha = '';
        //     throw ValidationException::withMessages([
        //         'captcha' => 'Captcha verification failed. Please try again.',
        //     ]);
        // }

        // Check credentials (but don't log in yet)
        $user = User::where('email', Str::lower($this->email))->first();
        
        if (!$user || !\Hash::check($this->password, $user->password)) {
            RateLimiter::hit($key, 60);
            $this->dispatch('captcha-error');
            $this->captcha = '';
            \Log::warning('LOGIN: Invalid credentials', ['email' => $this->email]);
            throw ValidationException::withMessages([
                'email' => 'These credentials do not match our records.',
            ]);
        }

        // Credentials are valid, send OTP
        $otpService = new OtpService();
        $result = $otpService->generateAndSend($this->email);

        if (!$result['success']) {
            throw ValidationException::withMessages([
                'email' => $result['message'],
            ]);
        }

        // Show OTP input
        $this->showOtpInput = true;
        $this->otpSent = true;
        $this->otpExpiresIn = 300; // 5 minutes in seconds
        
        session()->flash('message', 'OTP code sent to your email. Please check your inbox.');
        
        \Log::info('LOGIN: OTP sent', ['email' => $this->email]);
    }

    public function verifyOtp()
    {
        $this->validate();

        $otpService = new OtpService();
        $result = $otpService->verify($this->email, $this->otpCode);

        if (!$result['success']) {
            $this->otpCode = '';
            throw ValidationException::withMessages([
                'otpCode' => $result['message'],
            ]);
        }

        // OTP verified, now log in the user
        if (Auth::attempt(['email' => Str::lower($this->email), 'password' => $this->password], $this->remember)) {
            $key = 'login:' . Str::lower($this->email) . '|' . request()->ip();
            RateLimiter::clear($key);
            request()->session()->regenerate();
            
            \Log::info('LOGIN: Success with OTP', ['email' => $this->email, 'user_id' => Auth::id()]);

            return redirect()->intended(route('home'));
        }

        throw ValidationException::withMessages([
            'otpCode' => 'OTP authentication failed. Please try again.',
        ]);
    }

    public function resendOtp()
    {
        $otpService = new OtpService();
        $result = $otpService->generateAndSend($this->email);

        if ($result['success']) {
            $this->otpExpiresIn = 300;
            session()->flash('message', 'New OTP code sent to your email.');
        } else {
            session()->flash('error', $result['message']);
        }
    }

    public function cancelOtp()
    {
        $this->reset(['showOtpInput', 'otpSent', 'otpCode', 'otpExpiresIn', 'captcha']);
        $this->dispatch('captcha-error'); // Reset captcha
    }

    public function render()
    {
        return view('livewire.pages.auth.login');
    }
}
