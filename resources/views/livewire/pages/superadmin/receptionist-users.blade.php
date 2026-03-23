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
                class="px-5 py-2.5 bg-blue-600 text-white rounded-xl shadow-sm hover:bg-blue-700 transition">
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
                        focus:ring-2 focus:ring-blue-500 focus:outline-none transition">

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
                    <svg class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>

            </div>

            {{-- LOADING BAR --}}
            <div wire:loading wire:target="search" class="mt-2">
                <div class="w-full bg-gray-200 rounded-full h-1 overflow-hidden">
                    <div class="bg-blue-600 h-1 rounded-full animate-loading-bar"></div>
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
                                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-semibold">
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
                                        class="px-3 py-1 bg-blue-100 text-blue-700 rounded-md hover:bg-blue-200 text-sm">
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
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50">

            <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl border border-gray-200">

                <h2 class="text-xl font-semibold text-gray-900 mb-5">
                    {{ $editMode ? 'Edit Receptionist' : 'Create Receptionist' }}
                </h2>

                <form wire:submit.prevent="save" class="space-y-4">

                    {{-- NAME --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                        <input type="text" wire:model="name"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg
                                   text-gray-900 focus:ring-2 focus:ring-blue-500">
                        @error('name') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>

                    {{-- EMAIL --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" wire:model="email"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg
                                   text-gray-900 focus:ring-2 focus:ring-blue-500">
                        @error('email') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>

                    {{-- PASSWORD --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Password {{ $editMode ? '(optional)' : '' }}
                        </label>
                        <input type="password" wire:model="password"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg
                                   text-gray-900 focus:ring-2 focus:ring-blue-500">
                        @error('password') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>

                    {{-- STATUS --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select wire:model="status"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg text-gray-900 focus:ring-2 focus:ring-blue-500">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>

                    {{-- ACTIONS --}}
                    <div class="flex gap-3 pt-4">
                        <button type="submit"
                            class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            {{ $editMode ? 'Update' : 'Create' }}
                        </button>

                        <button type="button" wire:click="closeModal"
                            class="flex-1 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                            Cancel
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
