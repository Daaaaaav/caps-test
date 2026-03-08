<div class="min-h-screen h-screen flex">
    <div class="flex-1 bg-white flex items-center justify-center px-6 md:px-8 py-6">
        <div class="w-full max-w-md h-[calc(100vh-3.5rem)] flex flex-col justify-center">
            <div class="text-center mb-6">
                <img src="{{ asset('https://tiketkebunraya.id/assets/images/kebun-raya.png') }}" alt="Kebun Raya"
                    class="mx-auto mb-4 h-14 hover:scale-105 transition-transform duration-300" />
                <h2 class="text-gray-800 text-base font-light tracking-wide mb-1">KEBUN RAYA</h2>
                <p class="text-gray-500 text-sm font-medium tracking-wider">CREATE YOUR ACCOUNT</p>
                <div class="mt-3 w-12 h-0.5 bg-gray-800 mx-auto"></div>
            </div>

            {{-- Livewire form --}}
            <form x-data="{ showPassword: false, showPasswordConfirmation: false }" class="space-y-4"
                wire:submit.prevent="register">
                @csrf

                {{-- Full Name --}}
                <div class="group">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                    <input type="text" id="name" name="name" wire:model.defer="full_name" required
                        class="w-full px-0 py-2 text-gray-900 placeholder-gray-400 border-0 border-b-2 border-gray-200 bg-transparent focus:outline-none focus:border-gray-800 focus:ring-0 transition-all duration-300 group-hover:border-gray-400"
                        placeholder="Enter your full name">
                    @error('full_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Email --}}
                <div class="group">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                    <input type="email" id="email" name="email" wire:model.defer="email" required autocomplete="email"
                        class="w-full px-0 py-2 text-gray-900 placeholder-gray-400 border-0 border-b-2 border-gray-200 bg-transparent focus:outline-none focus:border-gray-800 focus:ring-0 transition-all duration-300 group-hover:border-gray-400"
                        placeholder="Enter your email address">
                    @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Phone Number --}}
                <div class="group">
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                    <input type="tel" id="phone" name="phone" wire:model.defer="phone_number" {{-- kalau ingin wajib:
                        tambahkan required --}}
                        class="w-full px-0 py-2 text-gray-900 placeholder-gray-400 border-0 border-b-2 border-gray-200 bg-transparent focus:outline-none focus:border-gray-800 focus:ring-0 transition-all duration-300 group-hover:border-gray-400"
                        placeholder="Enter your phone number">
                    @error('phone_number') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Company --}}
                <div class="group">
                    <label for="company" class="block text-sm font-medium text-gray-700 mb-2">Company</label>
                    <select id="company" name="company" wire:model.live="company_id"
                        class="w-full px-0 py-2 bg-transparent border-0 border-b-2 border-gray-200 text-gray-900 focus:outline-none focus:border-gray-800 focus:ring-0 transition-all duration-300">
                        <option value="">— Select company —</option>
                        @foreach($this->companies as $c)
                            <option value="{{ $c['id'] }}">{{ $c['name'] }}</option>
                        @endforeach
                    </select>
                    @error('company_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Department (filtered by company) --}}
                <div class="group">
                    <label for="department" class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                    <select id="department" name="department" wire:model.defer="department_id"
                        @disabled(empty($this->departments))
                        class="w-full px-0 py-2 bg-transparent border-0 border-b-2 border-gray-200 text-gray-900 focus:outline-none focus:border-gray-800 focus:ring-0 transition-all duration-300 disabled:text-gray-400 disabled:cursor-not-allowed">
                        <option value="">— Select department —</option>
                        @foreach($this->departments as $d)
                            <option value="{{ $d['id'] }}">{{ $d['name'] }}</option>
                        @endforeach
                    </select>
                    @error('department_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Password --}}
                <div class="group">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                    <div class="relative">
                        <input :type="showPassword ? 'text' : 'password'" id="password" name="password"
                            wire:model.defer="password" required autocomplete="new-password"
                            class="w-full px-0 py-2 pr-10 text-gray-900 placeholder-gray-400 border-0 border-b-2 border-gray-200 bg-transparent focus:outline-none focus:border-gray-800 focus:ring-0 transition-all duration-300 group-hover:border-gray-400"
                            placeholder="Enter your password">
                        <button type="button" @click="showPassword = !showPassword"
                            class="absolute inset-y-0 right-0 flex items-center text-gray-400 hover:text-gray-800 transition-colors duration-200">
                            {{-- svg tetap --}}
                            <svg x-show="!showPassword" class="h-5 w-5" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg x-show="showPassword" class="h-5 w-5" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" />
                            </svg>
                        </button>
                    </div>
                    @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Confirm Password --}}
                <div class="group">
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Confirm
                        Password</label>
                    <div class="relative">
                        <input :type="showPasswordConfirmation ? 'text' : 'password'" id="password_confirmation"
                            name="password_confirmation" wire:model.defer="password_confirmation" required
                            autocomplete="new-password"
                            class="w-full px-0 py-2 pr-10 text-gray-900 placeholder-gray-400 border-0 border-b-2 border-gray-200 bg-transparent focus:outline-none focus:border-gray-800 focus:ring-0 transition-all duration-300 group-hover:border-gray-400"
                            placeholder="Confirm your password">
                        <button type="button" @click="showPasswordConfirmation = !showPasswordConfirmation"
                            class="absolute inset-y-0 right-0 flex items-center text-gray-400 hover:text-gray-800 transition-colors duration-200">
                            {{-- svg tetap --}}
                            <svg x-show="!showPasswordConfirmation" class="h-5 w-5" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg x-show="showPasswordConfirmation" class="h-5 w-5" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" />
                            </svg>
                        </button>
                    </div>
                    @error('password_confirmation') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <button type="submit" wire:loading.attr="disabled"
                    class="w-full mt-6 rounded-2xl bg-black text-white py-3 px-6 font-medium tracking-wide hover:bg-red-800 focus:outline-none focus:ring-4 focus:ring-gray-300 focus:ring-opacity-50 transition-all duration-300">
                    <span wire:loading.remove>CREATE ACCOUNT</span>
                    <span wire:loading>Processing...</span>
                </button>

                <div class="flex justify-center items-center mt-6 pt-4 border-t border-gray-100">
                    <a href="{{ route('login') }}"
                        class="text-gray-500 hover:text-gray-800 transition-colors duration-200 text-sm font-medium">
                        Already have an account? Sign in
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="relative hidden md:block flex-1">
        <img src="{{ asset('images/login.jpg') }}" alt="Background"
            class="absolute inset-0 w-full h-full object-cover object-center select-none" draggable="false"
            loading="lazy" />
        <div class="absolute inset-0 bg-black/20"></div>
    </div>
</div>