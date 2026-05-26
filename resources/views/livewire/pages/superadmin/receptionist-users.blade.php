<div class="min-h-screen bg-gray-50">
    <main class="max-w-7xl mx-auto px-4 sm:px-6 py-8 space-y-8">

        {{-- ================= HEADER ================= --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">
                    Receptionist Users
                </h1>
                <p class="text-sm text-gray-500">
                    Manage receptionist accounts
                </p>
            </div>

            <button wire:click="openCreateModal"
                class="px-5 py-2.5 bg-gray-900 text-white rounded-xl shadow-sm hover:bg-gray-800 transition">
                + Add Receptionist
            </button>
        </div>


        {{-- ================= SEARCH ================= --}}
        <div class="bg-white border border-gray-200 rounded-2xl p-4 shadow-sm">

            <div class="relative flex items-center">

                {{-- ICON --}}
                <div class="absolute left-3 text-gray-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-4.35-4.35m1.6-5.65a7.25 7.25 0 11-14.5 0 7.25 7.25 0 0114.5 0z" />
                    </svg>
                </div>

                {{-- INPUT --}}
                <input type="text"
                    wire:model.live.debounce.500ms="search"
                    placeholder="Search receptionist by name or email..."
                    class="w-full pl-10 pr-20 py-2 rounded-lg border border-gray-300
                        text-gray-900 placeholder-gray-400
                        focus:ring-2 focus:ring-gray-500 focus:outline-none transition">

                {{-- CLEAR BUTTON --}}
                @if($search)
                    <button wire:click="$set('search', '')"
                        class="absolute right-12 text-gray-400 hover:text-gray-600 transition">
                        ✕
                    </button>
                @endif

                {{-- ⏳ LOADING SPINNER --}}
                <div wire:loading wire:target="search"
                    class="absolute right-3">
                    <svg class="animate-spin h-5 w-5 text-gray-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>

            </div>

            {{-- LOADING BAR --}}
            <div wire:loading wire:target="search" class="mt-2">
                <div class="w-full bg-gray-200 rounded-full h-1 overflow-hidden">
                    <div class="bg-gray-600 h-1 rounded-full animate-loading-bar"></div>
                </div>
            </div>

        </div>


        {{-- ================= TABLE ================= --}}
        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">

            <table class="w-full text-sm">
                
                {{-- TABLE HEAD --}}
                <thead class="bg-gray-50 text-gray-500 uppercase text-xs border-b">
                    <tr>
                        <th class="px-6 py-3 text-left">Name</th>
                        <th class="px-6 py-3 text-left">Email</th>
                        <th class="px-6 py-3 text-left">Status</th>
                        <th class="px-6 py-3 text-left">Joined</th>
                        <th class="px-6 py-3 text-left">Actions</th>
                    </tr>
                </thead>

                {{-- TABLE BODY --}}
                <tbody class="divide-y divide-gray-200">
                    @forelse($receptionists as $user)
                        <tr class="hover:bg-gray-50 transition">

                            {{-- NAME --}}
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center text-gray-700 font-semibold">
                                        {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                                    </div>
                                    <span class="font-medium text-gray-900">
                                        {{ $user->name ?? 'Unknown' }}
                                    </span>
                                </div>
                            </td>

                            {{-- EMAIL --}}
                            <td class="px-6 py-4 text-gray-600">
                                {{ $user->email }}
                            </td>

                            {{-- STATUS --}}
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-full text-xs font-medium
                                    {{ $user->status === 'active'
                                        ? 'bg-green-100 text-green-700'
                                        : 'bg-gray-100 text-gray-700' }}">
                                    {{ ucfirst($user->status ?? 'active') }}
                                </span>
                            </td>

                            {{-- DATE --}}
                            <td class="px-6 py-4 text-gray-600">
                                {{ $user->created_at?->format('M d, Y') ?? 'N/A' }}
                            </td>

                            {{-- ACTIONS --}}
                            <td class="px-6 py-4">
                                <div class="flex gap-2">

                                    <button wire:click="openEditModal({{ $user->user_id }})"
                                        class="px-3 py-1 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 text-sm">
                                        Edit
                                    </button>

                                    <button wire:click="delete({{ $user->user_id }})"
                                        wire:confirm="Are you sure?"
                                        class="px-3 py-1 bg-red-100 text-red-700 rounded-md hover:bg-red-200 text-sm">
                                        Delete
                                    </button>

                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                                No receptionists found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </main>


    {{-- ================= MODAL ================= --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            {{-- Backdrop --}}
            <div class="absolute inset-0 bg-black/60 backdrop-blur-md transition-opacity duration-300" wire:click="closeModal"></div>

            {{-- Modal Content --}}
            <div class="relative w-full max-w-md bg-card rounded-2xl border border-border shadow-2xl overflow-hidden flex flex-col">
                {{-- Header --}}
                <div class="px-6 py-5 border-b border-border bg-muted/10 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center">
                            <x-heroicon-o-user-plus class="w-4 h-4 text-primary" />
                        </div>
                        <h3 class="font-bold text-foreground text-base tracking-tight">
                            {{ $editMode ? 'Edit Receptionist' : 'Create Receptionist' }}
                        </h3>
                    </div>
                    <button type="button" class="w-8 h-8 flex items-center justify-center rounded-lg text-muted-foreground hover:text-foreground hover:bg-muted transition" wire:click="closeModal">✕</button>
                </div>

                <form wire:submit.prevent="save">
                    {{-- Body --}}
                    <div class="p-6 space-y-4">
                        {{-- NAME --}}
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-muted-foreground mb-1.5">Name</label>
                            <input type="text" wire:model="name"
                                class="w-full h-10 px-3.5 rounded-lg border border-input bg-background text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                            @error('name') <p class="text-xs text-destructive mt-1.5 font-medium">{{ $message }}</p> @enderror
                        </div>

                        {{-- EMAIL --}}
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-muted-foreground mb-1.5">Email</label>
                            <input type="email" wire:model="email"
                                class="w-full h-10 px-3.5 rounded-lg border border-input bg-background text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                            @error('email') <p class="text-xs text-destructive mt-1.5 font-medium">{{ $message }}</p> @enderror
                        </div>

                        {{-- PHONE --}}
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-muted-foreground mb-1.5">Phone Number <span class="text-muted-foreground/60 font-normal">(optional)</span></label>
                            <input type="text" wire:model="phone" placeholder="e.g. 08123456789"
                                class="w-full h-10 px-3.5 rounded-lg border border-input bg-background text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                            @error('phone') <p class="text-xs text-destructive mt-1.5 font-medium">{{ $message }}</p> @enderror
                        </div>

                        {{-- PASSWORD --}}
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-muted-foreground mb-1.5">
                                Password {{ $editMode ? '(optional)' : '' }}
                            </label>
                            <input type="password" wire:model="password"
                                class="w-full h-10 px-3.5 rounded-lg border border-input bg-background text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                            @error('password') <p class="text-xs text-destructive mt-1.5 font-medium">{{ $message }}</p> @enderror
                        </div>

                        {{-- STATUS --}}
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-muted-foreground mb-1.5">Status</label>
                            <select wire:model="status"
                                class="w-full h-10 px-3.5 rounded-lg border border-input bg-background text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="border-t border-border px-6 py-4 flex items-center justify-end gap-3 bg-muted/5">
                        <button type="button" wire:click="closeModal"
                            class="h-9 px-4 rounded-lg bg-secondary text-secondary-foreground text-xs font-semibold hover:bg-secondary/80 border border-border transition inline-flex items-center gap-1.5">
                            <x-heroicon-o-arrow-uturn-left class="w-3.5 h-3.5" />
                            <span>Batal</span>
                        </button>
                        <button type="submit"
                            class="h-9 px-4 rounded-lg bg-primary text-primary-foreground text-xs font-semibold hover:bg-primary/95 transition shadow-sm inline-flex items-center gap-1.5">
                            <x-heroicon-o-check class="w-3.5 h-3.5" />
                            <span>{{ $editMode ? 'Update' : 'Create' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <style>
        @keyframes loading-bar {
            0% {
                width: 0%;
            }
            50% {
                width: 70%;
            }
            100% {
                width: 100%;
            }
        }

        .animate-loading-bar {
            animation: loading-bar 1s ease-in-out infinite;
        }
    </style>
</div>
