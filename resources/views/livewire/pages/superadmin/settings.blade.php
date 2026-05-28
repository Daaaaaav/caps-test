<div class="min-h-screen bg-[#f5f7f2]">
    <main class="max-w-3xl mx-auto px-4 sm:px-6 py-8 space-y-8">

        {{-- ===== HEADER ===== --}}
        <div>
            <h1 class="text-2xl font-semibold text-[#2d3a24]">{{ __('app.settings_title') }}</h1>
            <p class="text-sm text-[#7a8f6a] mt-1">{{ __('app.settings_manage_sub') }}</p>
        </div>

        {{-- ===== ALERTS ===== --}}
        @if($successMessage)
            <div class="flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 text-sm">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                {{ $successMessage }}
            </div>
        @endif

        @if($errorMessage)
            <div class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 text-sm">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01M12 5a7 7 0 100 14A7 7 0 0012 5z" />
                </svg>
                {{ $errorMessage }}
            </div>
        @endif

        {{-- ===== PROFILE CARD ===== --}}
        <div class="bg-white border border-[#d4dfc8] rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-[#e4edd8] bg-[#f0f4eb]">
                <h2 class="text-base font-semibold text-[#2d3a24]">{{ __('app.profile_information') }}</h2>
                <p class="text-xs text-[#7a8f6a] mt-0.5">{{ __('app.profile_info_sub') }}</p>
            </div>

            <form wire:submit.prevent="updateProfile" class="px-6 py-6 space-y-5">

                {{-- Name --}}
                <div>
                    <label class="block text-sm font-medium text-[#4E653D] mb-1">{{ __('app.full_name_label') }}</label>
                    <input type="text" wire:model="name"
                        class="w-full px-4 py-2.5 border border-[#c4d4b4] rounded-xl text-[#2d3a24]
                               focus:ring-2 focus:ring-[#4E653D] focus:outline-none transition"
                        placeholder="{{ __('app.full_name_ph') }}">
                    @error('name')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Email --}}
                <div>
                    <label class="block text-sm font-medium text-[#4E653D] mb-1">{{ __('app.email_address') }}</label>
                    <input type="email" wire:model="email"
                        class="w-full px-4 py-2.5 border border-[#c4d4b4] rounded-xl text-[#2d3a24]
                               focus:ring-2 focus:ring-[#4E653D] focus:outline-none transition"
                        placeholder="you@example.com">
                    @error('email')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Phone --}}
                <div>
                    <label class="block text-sm font-medium text-[#4E653D] mb-1">
                        {{ __('app.phone_optional_label') }}
                    </label>
                    <input type="text" wire:model="phone"
                        class="w-full px-4 py-2.5 border border-[#c4d4b4] rounded-xl text-[#2d3a24]
                               focus:ring-2 focus:ring-[#4E653D] focus:outline-none transition"
                        placeholder="e.g. 08123456789">
                    @error('phone')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-2">
                    <button type="submit"
                        class="px-6 py-2.5 bg-[#4A2F24] text-white text-sm font-medium rounded-xl
                               hover:bg-[#3d2720] transition shadow-sm">
                        {{ __('app.save_profile') }}
                    </button>
                </div>
            </form>
        </div>

        {{-- ===== PASSWORD CARD ===== --}}
        <div class="bg-white border border-[#d4dfc8] rounded-2xl shadow-sm overflow-hidden">
            <button wire:click="$toggle('showPasswordSection')"
                class="w-full flex items-center justify-between px-6 py-4 border-b border-[#e4edd8] bg-[#f0f4eb]
                       hover:bg-[#eef1e8] transition text-left">
                <div>
                    <h2 class="text-base font-semibold text-[#2d3a24]">{{ __('app.change_password') }}</h2>
                    <p class="text-xs text-[#7a8f6a] mt-0.5">{{ __('app.change_password_sub') }}</p>
                </div>
                <svg class="w-5 h-5 text-[#9aaa8a] transition-transform {{ $showPasswordSection ? 'rotate-180' : '' }}"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            @if($showPasswordSection)
                <form wire:submit.prevent="updatePassword" class="px-6 py-6 space-y-5">

                    {{-- Current Password --}}
                    <div>
                        <label class="block text-sm font-medium text-[#4E653D] mb-1">{{ __('app.current_password') }}</label>
                        <input type="password" wire:model="currentPassword"
                            class="w-full px-4 py-2.5 border border-[#c4d4b4] rounded-xl text-[#2d3a24]
                                   focus:ring-2 focus:ring-[#4E653D] focus:outline-none transition">
                        @error('currentPassword')
                            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- New Password --}}
                    <div>
                        <label class="block text-sm font-medium text-[#4E653D] mb-1">{{ __('app.new_password') }}</label>
                        <input type="password" wire:model="newPassword"
                            class="w-full px-4 py-2.5 border border-[#c4d4b4] rounded-xl text-[#2d3a24]
                                   focus:ring-2 focus:ring-[#4E653D] focus:outline-none transition">
                        @error('newPassword')
                            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Confirm Password --}}
                    <div>
                        <label class="block text-sm font-medium text-[#4E653D] mb-1">{{ __('app.confirm_new_password') }}</label>
                        <input type="password" wire:model="confirmPassword"
                            class="w-full px-4 py-2.5 border border-[#c4d4b4] rounded-xl text-[#2d3a24]
                                   focus:ring-2 focus:ring-[#4E653D] focus:outline-none transition">
                        @error('confirmPassword')
                            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="pt-2 flex gap-3">
                        <button type="submit"
                            class="px-6 py-2.5 bg-[#4A2F24] text-white text-sm font-medium rounded-xl
                                   hover:bg-[#3d2720] transition shadow-sm">
                            {{ __('app.update_password') }}
                        </button>
                        <button type="button" wire:click="$set('showPasswordSection', false)"
                            class="px-6 py-2.5 bg-[#eef1e8] text-[#4E653D] text-sm font-medium rounded-xl
                                   hover:bg-[#dde4d4] transition">
                            {{ __('app.cancel') }}
                        </button>
                    </div>
                </form>
            @endif
        </div>

    </main>
</div>
