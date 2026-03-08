<div
    x-data="{
        profile: @js($profile),
        stats: @js($stats),
        passwordSuccess: false,
        initials() {
            const n = (this.profile?.fullName || 'U').trim();
            const parts = n.split(/\s+/);
            return (parts[0]?.[0] || 'U') + (parts[1]?.[0] || '');
        }
    }"
    x-on:password-updated.window="
        passwordSuccess = true;
        setTimeout(() => passwordSuccess = false, 3000);
    "
    class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    {{-- Header --}}
    <div class="bg-white rounded-xl shadow-sm border-2 border-black p-4 md:p-6 mb-4 md:mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <h1 class="text-xl md:text-2xl font-bold text-gray-900">User Profile</h1>

            <a href="{{ route('home') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Home
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border-2 border-black p-4 md:p-5">
                {{-- User Header Card --}}
                <div class="flex items-center gap-6 p-6 bg-gray-50 rounded-xl border border-gray-200 mb-6">
                    <div class="shrink-0">
                        <div class="w-20 h-20 bg-gray-900 rounded-full flex items-center justify-center text-white text-2xl font-bold ring-4 ring-white shadow-sm"
                            x-text="initials().toUpperCase()">
                        </div>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900" x-text="profile.fullName"></h2>
                        <p class="text-gray-600 font-medium" x-text="profile.email"></p>
                        <div class="flex items-center gap-2 mt-2">
                            <span class="relative flex h-3 w-3">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                            </span>
                            <span class="text-xs font-bold text-green-700 uppercase tracking-wide">Active Now</span>
                        </div>
                    </div>
                </div>

                <div class="mb-6 border-b border-gray-100 pb-4">
                    <h3 class="text-lg font-bold text-gray-900">Personal Information</h3>
                    <p class="text-sm text-gray-500">Manage your personal information, including phone numbers and email address.</p>
                </div>

                <div class="space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Full Name</label>
                            <div class="w-full px-3 py-2.5 text-sm text-gray-700 bg-gray-50 border border-gray-200 rounded-lg" x-text="profile.fullName"></div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Email Address</label>
                            <div class="w-full px-3 py-2.5 text-sm text-gray-700 bg-gray-50 border border-gray-200 rounded-lg" x-text="profile.email"></div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Phone Number</label>
                            <div class="w-full px-3 py-2.5 text-sm text-gray-700 bg-gray-50 border border-gray-200 rounded-lg" x-text="profile.phone_number || 'Not provided'"></div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Employee ID</label>
                            <div class="w-full px-3 py-2.5 text-sm text-gray-700 bg-gray-50 border border-gray-200 rounded-lg" x-text="profile.employeeId || '-'"></div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Department</label>
                            <div class="w-full px-3 py-2.5 text-sm text-gray-700 bg-gray-50 border border-gray-200 rounded-lg flex items-center gap-2">
                                <x-heroicon-o-building-office-2 class="w-4 h-4 text-gray-400" />
                                <span x-text="profile.department"></span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Branch</label>
                            <div class="w-full px-3 py-2.5 text-sm text-gray-700 bg-gray-50 border border-gray-200 rounded-lg flex items-center gap-2">
                                <x-heroicon-o-map-pin class="w-4 h-4 text-gray-400" />
                                <span x-text="profile.company"></span>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Role</label>
                            <div class="w-full px-3 py-2.5 text-sm text-gray-700 bg-gray-50 border border-gray-200 rounded-lg flex items-center gap-2">
                                <x-heroicon-o-shield-check class="w-4 h-4 text-gray-400" />
                                <span x-text="profile.role || '-'"></span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Join Date</label>
                            <div class="w-full px-3 py-2.5 text-sm text-gray-700 bg-gray-50 border border-gray-200 rounded-lg flex items-center gap-2">
                                <x-heroicon-o-calendar class="w-4 h-4 text-gray-400" />
                                <span x-text="profile.joinDate || '-'"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white rounded-xl shadow-sm border-2 border-black p-4 md:p-5">
                <div class="mb-4 border-b border-gray-100 pb-4">
                    <h2 class="text-lg font-bold text-gray-900">Change Password</h2>
                    <p class="text-xs text-gray-500 mt-1">Use a secure password to protect your account.</p>
                </div>

                <div x-show="passwordSuccess" x-transition
                    class="mb-4 p-3 bg-emerald-50 text-emerald-700 text-sm rounded-lg border border-emerald-200 flex items-center gap-2"
                    style="display:none;">
                    <x-heroicon-o-check-circle class="w-5 h-5" />
                    Password updated successfully!
                </div>

                @error('current_password')
                <div class="mb-4 p-3 bg-red-50 text-red-700 text-sm rounded-lg border border-red-200 flex items-center gap-2">
                    <x-heroicon-o-exclamation-circle class="w-5 h-5" /> {{ $message }}
                </div>
                @enderror
                @error('new_password')
                <div class="mb-4 p-3 bg-red-50 text-red-700 text-sm rounded-lg border border-red-200 flex items-center gap-2">
                    <x-heroicon-o-exclamation-circle class="w-5 h-5" /> {{ $message }}
                </div>
                @enderror

                <form wire:submit.prevent="updatePassword" class="space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-900 mb-1.5">Current Password</label>
                        <input type="password" wire:model.defer="current_password" autocomplete="current-password" required
                            class="w-full px-3 py-2 text-sm text-gray-900 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-900 mb-1.5">New Password</label>
                        <input type="password" wire:model.defer="new_password" autocomplete="new-password" required
                            class="w-full px-3 py-2 text-sm text-gray-900 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent">
                        <p class="text-[10px] text-gray-500 mt-1">Min. 8 chars, different from current.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-900 mb-1.5">Confirm New Password</label>
                        <input type="password" wire:model.defer="new_password_confirmation" autocomplete="new-password" required
                            class="w-full px-3 py-2 text-sm text-gray-900 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent">
                    </div>

                    <button type="submit" class="w-full px-4 py-2 bg-gray-900 text-white text-sm font-medium rounded-lg hover:bg-gray-800 transition-colors flex justify-center items-center gap-2" wire:loading.attr="disabled">
                        <span wire:loading.remove>Change Password</span>
                        <span wire:loading>Processing...</span>
                    </button>
                </form>
            </div>

            <div class="bg-white rounded-xl shadow-sm border-2 border-black p-4 md:p-5">
                <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <x-heroicon-o-chart-bar class="w-5 h-5" />
                    Account Summary
                </h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center py-2 border-b border-dashed border-gray-200">
                        <span class="text-sm text-gray-600 flex items-center gap-2">
                            <x-heroicon-o-ticket class="w-4 h-4" /> Total Tickets
                        </span>
                        <span class="text-sm font-bold text-yellow-600 bg-yellow-50 px-2 py-0.5 rounded"
                            x-text="stats.totalTickets"></span>
                    </div>

                    <div class="flex justify-between items-center py-2 border-b border-dashed border-gray-200">
                        <span class="text-sm text-gray-600 flex items-center gap-2">
                            <x-heroicon-o-calendar-days class="w-4 h-4" /> Total Book Rooms
                        </span>
                        <span class="text-sm font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded"
                            x-text="stats.totalBookRooms"></span>
                    </div>

                    <div class="flex justify-between items-center py-2 border-b border-dashed border-gray-200">
                        <span class="text-sm text-gray-600 flex items-center gap-2">
                            <x-heroicon-o-cube class="w-4 h-4" /> Total Book Vehicle
                        </span>
                        <span class="text-sm font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded"
                            x-text="stats.totalBookVehicle"></span>
                    </div>

                    <div class="flex justify-between items-center py-2">
                        <span class="text-sm text-gray-600 flex items-center gap-2">
                            <x-heroicon-o-clock class="w-4 h-4" /> Member Since
                        </span>
                        <span class="text-xs font-bold text-gray-900 uppercase tracking-wide"
                            x-text="stats.memberSince"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>