<div>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>

<div class="min-h-screen flex">
    <div class="relative hidden md:block flex-1">
        <img src="{{ asset('images/login.jpg') }}" alt="Background"
            class="absolute inset-0 w-full h-full object-cover object-center select-none" draggable="false" />
        <div class="absolute inset-0 bg-black/20"></div>
    </div>

    <div class="flex-1 bg-white flex items-center justify-center px-8">
        <div class="w-full max-w-md">
            <div class="text-center mb-12">
                <img src="{{ asset('https://tiketkebunraya.id/assets/images/kebun-raya.png') }}" alt="Kebun Raya"
                    class="mx-auto mb-6 h-20 hover:scale-105 transition-transform duration-300" />
                <h2 class="text-gray-800 text-lg font-light tracking-wide mb-2">KEBUN RAYA</h2>
                <p class="text-gray-500 text-sm font-medium tracking-wider">WELCOME TO LOGIN</p>
                <div class="mt-4 w-16 h-0.5 bg-gray-800 mx-auto"></div>
            </div>

            <div x-data="{ showPassword: false }" class="space-y-8">
                <div class="group">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-3">Email Address</label>
                    <input type="email" id="email" wire:model.defer="email" autocomplete="email"
                        class="w-full px-0 py-3 text-gray-900 placeholder-gray-400 border-0 border-b-2 border-gray-200 bg-transparent focus:outline-none focus:border-gray-800 focus:ring-0 transition-all duration-300 group-hover:border-gray-400"
                        placeholder="Enter your email address">
                    @error('email')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="group">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-3">Password</label>
                    <div class="relative">
                        <input x-bind:type="showPassword ? 'text' : 'password'" id="password"
                            wire:model.defer="password" autocomplete="current-password"
                            class="w-full px-0 py-3 pr-10 text-gray-900 placeholder-gray-400 border-0 border-b-2 border-gray-200 bg-transparent focus:outline-none focus:border-gray-800 focus:ring-0 transition-all duration-300 group-hover:border-gray-400"
                            placeholder="Enter your password">
                        <button type="button" x-on:click="showPassword = !showPassword"
                            class="absolute inset-y-0 right-0 flex items-center text-gray-400 hover:text-gray-800 transition-colors duration-200">
                            <svg x-show="!showPassword" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg x-show="showPassword" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" />
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between">
                    <label class="inline-flex items-center gap-2 text-sm text-gray-600 select-none cursor-pointer">
                        <input type="checkbox" wire:model.defer="remember"
                            class="h-4 w-4 rounded border-gray-300 text-black focus:ring-gray-800">
                        <span>Remember me</span>
                    </label>

                    <a href="#" class="text-gray-500 hover:text-gray-800 transition-colors duration-200 text-sm font-medium">
                        Forgot password?
                    </a>
                </div>

                <div wire:ignore class="mt-6" id="recaptcha-container">
                    <div class="g-recaptcha"
                        data-sitekey="{{ config('services.recaptcha.site_key') }}"
                        data-callback="onCaptchaSuccess"
                        data-expired-callback="onCaptchaExpired">
                    </div>
                    @error('captcha')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <button type="button" wire:click="login"
                    class="w-full rounded-3xl mt-6 bg-black text-white py-4 px-6 font-medium tracking-wide hover:bg-red-800 focus:outline-none focus:ring-4 focus:ring-gray-300 focus:ring-opacity-50 transform hover:scale-[1.02] transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed"
                    wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="login"> SIGN IN </span>
                    <span wire:loading wire:target="login"> Processing... </span>
                </button>
            </div>

            <!-- @script
            <script>
                // Debug logging
                console.log('=== LOGIN PAGE SCRIPT LOADED ===');
                console.log('Livewire available:', typeof Livewire !== 'undefined');
                console.log('Alpine available:', typeof Alpine !== 'undefined');
                console.log('$wire available:', typeof $wire !== 'undefined');
                console.log('Component ID:', '{{ $this->getId() }}');

                window.onCaptchaSuccess = function(token) {
                    console.log('Captcha success, token length:', token.length);
                    try {
                        $wire.set('captcha', token);
                        console.log('Captcha token set successfully via $wire');
                    } catch (e) {
                        console.error('Failed to set captcha via $wire:', e);
                        // Fallback: try using Livewire directly
                        try {
                            Livewire.find('{{ $this->getId() }}').set('captcha', token);
                            console.log('Captcha token set successfully via Livewire.find');
                        } catch (e2) {
                            console.error('Failed to set captcha via Livewire.find:', e2);
                        }
                    }
                };

                window.onCaptchaExpired = function() {
                    console.log('Captcha expired');
                    try {
                        $wire.set('captcha', '');
                    } catch (e) {
                        console.error('Failed to clear captcha:', e);
                    }
                };

                // Listen for captcha error event to reset the widget
                if (typeof Livewire !== 'undefined') {
                    Livewire.on('captcha-error', () => {
                        console.log('Captcha error event received, resetting widget');
                        if (typeof grecaptcha !== 'undefined') {
                            grecaptcha.reset();
                        }
                    });
                } else {
                    console.error('Livewire is not available for event listener!');
                }
            </script>
            @endscript -->
        </div>
    </div>
</div>
</div>
