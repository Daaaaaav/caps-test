<div class="min-h-screen bg-[#f5f7f2]">
    <main class="max-w-3xl mx-auto px-4 sm:px-6 py-8 space-y-6">

        {{-- ===== HEADER ===== --}}
        <div>
            <h1 class="text-2xl font-semibold text-[#2d3a24]">{{ __('app.settings_title') }}</h1>
            <p class="text-sm text-[#7a8f6a] mt-1">{{ __('app.settings_manage_sub') }}</p>
        </div>

        {{-- ===== TAB SWITCHER ===== --}}
        <div class="flex gap-1 bg-[#e8ede0] rounded-xl p-1 w-fit">
            <button wire:click="$set('activeTab', 'profile')"
                class="px-5 py-2 text-sm font-medium rounded-lg transition
                    {{ $activeTab === 'profile'
                        ? 'bg-white text-[#2d3a24] shadow-sm'
                        : 'text-[#7a8f6a] hover:text-[#2d3a24]' }}">
                Profile
            </button>
            <button wire:click="$set('activeTab', 'ai')"
                class="px-5 py-2 text-sm font-medium rounded-lg transition
                    {{ $activeTab === 'ai'
                        ? 'bg-white text-[#2d3a24] shadow-sm'
                        : 'text-[#7a8f6a] hover:text-[#2d3a24]' }}">
                AI Model
            </button>
        </div>

        {{-- ================================================================ --}}
        {{-- PROFILE TAB                                                       --}}
        {{-- ================================================================ --}}
        @if($activeTab === 'profile')

            {{-- Alerts --}}
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

            {{-- Profile Card --}}
            <div class="bg-white border border-[#d4dfc8] rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-[#e4edd8] bg-[#f0f4eb]">
                    <h2 class="text-base font-semibold text-[#2d3a24]">{{ __('app.profile_information') }}</h2>
                    <p class="text-xs text-[#7a8f6a] mt-0.5">{{ __('app.profile_info_sub') }}</p>
                </div>

                <form wire:submit.prevent="updateProfile" class="px-6 py-6 space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-[#4E653D] mb-1">{{ __('app.full_name_label') }}</label>
                        <input type="text" wire:model="name"
                            class="w-full px-4 py-2.5 border border-[#c4d4b4] rounded-xl text-[#2d3a24]
                                   focus:ring-2 focus:ring-[#4E653D] focus:outline-none transition"
                            placeholder="{{ __('app.full_name_ph') }}">
                        @error('name') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-[#4E653D] mb-1">{{ __('app.email_address') }}</label>
                        <input type="email" wire:model="email"
                            class="w-full px-4 py-2.5 border border-[#c4d4b4] rounded-xl text-[#2d3a24]
                                   focus:ring-2 focus:ring-[#4E653D] focus:outline-none transition"
                            placeholder="you@example.com">
                        @error('email') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-[#4E653D] mb-1">{{ __('app.phone_optional_label') }}</label>
                        <input type="text" wire:model="phone"
                            class="w-full px-4 py-2.5 border border-[#c4d4b4] rounded-xl text-[#2d3a24]
                                   focus:ring-2 focus:ring-[#4E653D] focus:outline-none transition"
                            placeholder="e.g. 08123456789">
                        @error('phone') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
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

            {{-- Password Card --}}
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
                        <div>
                            <label class="block text-sm font-medium text-[#4E653D] mb-1">{{ __('app.current_password') }}</label>
                            <input type="password" wire:model="currentPassword"
                                class="w-full px-4 py-2.5 border border-[#c4d4b4] rounded-xl text-[#2d3a24]
                                       focus:ring-2 focus:ring-[#4E653D] focus:outline-none transition">
                            @error('currentPassword') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-[#4E653D] mb-1">{{ __('app.new_password') }}</label>
                            <input type="password" wire:model="newPassword"
                                class="w-full px-4 py-2.5 border border-[#c4d4b4] rounded-xl text-[#2d3a24]
                                       focus:ring-2 focus:ring-[#4E653D] focus:outline-none transition">
                            @error('newPassword') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-[#4E653D] mb-1">{{ __('app.confirm_new_password') }}</label>
                            <input type="password" wire:model="confirmPassword"
                                class="w-full px-4 py-2.5 border border-[#c4d4b4] rounded-xl text-[#2d3a24]
                                       focus:ring-2 focus:ring-[#4E653D] focus:outline-none transition">
                            @error('confirmPassword') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
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

        @endif {{-- end profile tab --}}


        {{-- ================================================================ --}}
        {{-- AI MODEL TAB                                                      --}}
        {{-- ================================================================ --}}
        @if($activeTab === 'ai')

            {{-- AI Alerts --}}
            @if($aiSuccess)
                <div class="flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 text-sm">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    {{ $aiSuccess }}
                </div>
            @endif
            @if($aiError)
                <div class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 text-sm">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01M12 5a7 7 0 100 14A7 7 0 0012 5z" />
                    </svg>
                    {{ $aiError }}
                </div>
            @endif

            {{-- Info banner --}}
            <div class="flex gap-3 bg-blue-50 border border-blue-200 text-blue-800 rounded-xl px-4 py-3 text-sm">
                <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20A10 10 0 0012 2z" />
                </svg>
                <span>
                    These values replace all hardcoded parameters in the LSTM model, fallback forecasting,
                    and the decision engine. Changes take effect on the next prediction run.
                </span>
            </div>

            {{-- Group cards --}}
            @php
                $groupLabels = [
                    'lstm'     => ['LSTM Model Hyperparameters',   'Controls the neural network architecture and training behaviour.'],
                    'fallback' => ['Fallback Moving Average',       'Used when the LSTM service is unavailable.'],
                    'decision' => ['Decision Engine Thresholds',    'Risk scoring rules applied to booking requests.'],
                    'security' => ['Security & Spam Detection',     'Rate-limiting thresholds for form abuse detection.'],
                ];
            @endphp

            <form wire:submit.prevent="saveAISettings" class="space-y-6">

                @foreach($aiGrouped as $group => $fields)
                    @php [$groupTitle, $groupSub] = $groupLabels[$group] ?? [$group, '']; @endphp

                    <div class="bg-white border border-[#d4dfc8] rounded-2xl shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-[#e4edd8] bg-[#f0f4eb]">
                            <h2 class="text-base font-semibold text-[#2d3a24]">{{ $groupTitle }}</h2>
                            @if($groupSub)
                                <p class="text-xs text-[#7a8f6a] mt-0.5">{{ $groupSub }}</p>
                            @endif
                        </div>

                        <div class="px-6 py-5 grid grid-cols-1 sm:grid-cols-2 gap-5">
                            @foreach($fields as $key => $meta)
                                <div>
                                    <label class="block text-sm font-medium text-[#4E653D] mb-1">
                                        {{ $meta['label'] }}
                                    </label>
                                    <input
                                        type="number"
                                        step="{{ in_array($meta['type'], ['float']) ? 'any' : '1' }}"
                                        wire:model.lazy="aiSettings.{{ $key }}"
                                        class="w-full px-4 py-2.5 border border-[#c4d4b4] rounded-xl text-[#2d3a24]
                                               focus:ring-2 focus:ring-[#4E653D] focus:outline-none transition text-sm"
                                    >
                                    @error("aiSettings.{$key}")
                                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                    @if($meta['description'])
                                        <p class="text-xs text-[#9aaa8a] mt-1 leading-relaxed">{{ $meta['description'] }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                {{-- Action buttons --}}
                <div class="flex items-center gap-3 pt-2">
                    <button type="submit"
                        wire:loading.attr="disabled"
                        class="px-6 py-2.5 bg-[#4A2F24] text-white text-sm font-medium rounded-xl
                               hover:bg-[#3d2720] transition shadow-sm disabled:opacity-60">
                        <span wire:loading.remove wire:target="saveAISettings">Save AI Settings</span>
                        <span wire:loading wire:target="saveAISettings">Saving…</span>
                    </button>

                    <button type="button"
                        wire:click="resetAISettings"
                        wire:confirm="Reset all AI settings to their original defaults?"
                        wire:loading.attr="disabled"
                        class="px-6 py-2.5 bg-[#eef1e8] text-[#4E653D] text-sm font-medium rounded-xl
                               hover:bg-[#dde4d4] transition disabled:opacity-60">
                        Reset to Defaults
                    </button>
                </div>

            </form>

        @endif {{-- end ai tab --}}

    </main>
</div>
