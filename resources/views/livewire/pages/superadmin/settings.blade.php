<div class="min-h-screen bg-gray-50">
    <main class="max-w-3xl mx-auto px-4 sm:px-6 py-8 space-y-8">

        {{-- ===== HEADER ===== --}}
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Settings</h1>
            <p class="text-sm text-gray-500 mt-1">Manage your account profile and security preferences.</p>
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
        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                <h2 class="text-base font-semibold text-gray-800">Profile Information</h2>
                <p class="text-xs text-gray-500 mt-0.5">Update your display name, email, and phone number.</p>
            </div>

            <form wire:submit.prevent="updateProfile" class="px-6 py-6 space-y-5">

                {{-- Name --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                    <input type="text" wire:model="name"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-gray-900
                               focus:ring-2 focus:ring-gray-500 focus:outline-none transition"
                        placeholder="Your full name">
                    @error('name')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Email --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <input type="email" wire:model="email"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-gray-900
                               focus:ring-2 focus:ring-gray-500 focus:outline-none transition"
                        placeholder="you@example.com">
                    @error('email')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Phone --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Phone Number
                        <span class="text-gray-400 font-normal">(optional)</span>
                    </label>
                    <input type="text" wire:model="phone"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-gray-900
                               focus:ring-2 focus:ring-gray-500 focus:outline-none transition"
                        placeholder="e.g. 08123456789">
                    @error('phone')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-2">
                    <button type="submit"
                        class="px-6 py-2.5 bg-gray-900 text-white text-sm font-medium rounded-xl
                               hover:bg-gray-800 transition shadow-sm">
                        Save Profile
                    </button>
                </div>
            </form>
        </div>

        {{-- ===== PASSWORD CARD ===== --}}
        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
            <button wire:click="$toggle('showPasswordSection')"
                class="w-full flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50
                       hover:bg-gray-100 transition text-left">
                <div>
                    <h2 class="text-base font-semibold text-gray-800">Change Password</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Update your login password.</p>
                </div>
                <svg class="w-5 h-5 text-gray-400 transition-transform {{ $showPasswordSection ? 'rotate-180' : '' }}"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            @if($showPasswordSection)
                <form wire:submit.prevent="updatePassword" class="px-6 py-6 space-y-5">

                    {{-- Current Password --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                        <input type="password" wire:model="currentPassword"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-gray-900
                                   focus:ring-2 focus:ring-gray-500 focus:outline-none transition">
                        @error('currentPassword')
                            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- New Password --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                        <input type="password" wire:model="newPassword"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-gray-900
                                   focus:ring-2 focus:ring-gray-500 focus:outline-none transition">
                        @error('newPassword')
                            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Confirm Password --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                        <input type="password" wire:model="confirmPassword"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-gray-900
                                   focus:ring-2 focus:ring-gray-500 focus:outline-none transition">
                        @error('confirmPassword')
                            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="pt-2 flex gap-3">
                        <button type="submit"
                            class="px-6 py-2.5 bg-gray-900 text-white text-sm font-medium rounded-xl
                                   hover:bg-gray-800 transition shadow-sm">
                            Update Password
                        </button>
                        <button type="button" wire:click="$set('showPasswordSection', false)"
                            class="px-6 py-2.5 bg-gray-100 text-gray-700 text-sm font-medium rounded-xl
                                   hover:bg-gray-200 transition">
                            Cancel
                        </button>
                    </div>
                </form>
            @endif
        </div>

    </main>
</div>
