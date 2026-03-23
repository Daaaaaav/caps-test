<div class="min-h-screen bg-gray-50">
    <main class="px-4 sm:px-6 py-6 space-y-8">

        {{-- HEADER --}}
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Receptionist Users</h1>
                <p class="text-sm text-gray-500">Manage receptionist accounts</p>
            </div>

            <button wire:click="openCreateModal"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                + Add Receptionist
            </button>
        </div>


        {{-- SUCCESS MESSAGE --}}
        @if (session()->has('message'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                {{ session('message') }}
            </div>
        @endif


        {{-- STATS --}}
        <section class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            @foreach($stats as $stat)
                <div wire:click="setStatusFilter('{{ $stat['key'] }}')" 
                    class="cursor-pointer bg-white rounded-2xl p-5 shadow-sm hover:shadow-lg transition
                    {{ $statusFilter === $stat['key'] ? 'ring-2 ring-blue-500' : '' }}">
                    
                    <p class="text-sm text-gray-500">{{ $stat['label'] }}</p>
                    <h2 class="text-3xl font-bold mt-2 text-blue-600">{{ $stat['value'] }}</h2>
                </div>
            @endforeach
        </section>


        {{-- SEARCH --}}
        <div class="bg-white rounded-2xl p-4 shadow-sm">
            <input type="text" wire:model.live="search"
                placeholder="Search by name or email..."
                class="w-full px-4 py-2 border border-gray-300 rounded-lg
                       text-gray-900 bg-white placeholder-gray-400
                       focus:ring-2 focus:ring-blue-500 focus:outline-none">
        </div>


        {{-- USERS TABLE --}}
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Joined</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y">
                    @forelse($receptionists as $user)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-semibold">
                                        {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                                    </div>
                                    <span class="font-medium text-gray-900">{{ $user->name ?? 'Unknown' }}</span>
                                </div>
                            </td>

                            <td class="px-6 py-4 text-gray-600">{{ $user->email }}</td>

                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-full text-xs font-medium
                                    {{ $user->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                                    {{ ucfirst($user->status ?? 'active') }}
                                </span>
                            </td>

                            <td class="px-6 py-4 text-gray-600">
                                {{ $user->created_at?->format('M d, Y') ?? 'N/A' }}
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex gap-2">
                                    <button wire:click="openEditModal({{ $user->user_id }})"
                                        class="px-3 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200 text-sm">
                                        Edit
                                    </button>

                                    <button wire:click="delete({{ $user->user_id }})"
                                        wire:confirm="Are you sure you want to delete this receptionist?"
                                        class="px-3 py-1 bg-red-100 text-red-700 rounded hover:bg-red-200 text-sm">
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                No receptionists found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </main>


    {{-- MODAL --}}
    @if($showModal)
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50">

            <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl border border-gray-200">

                <h2 class="text-xl font-semibold text-gray-900 mb-4">
                    {{ $editMode ? 'Edit' : 'Create' }} Receptionist
                </h2>

                <form wire:submit.prevent="save" class="space-y-4">

                    {{-- NAME --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                        <input type="text" wire:model="name"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg
                                   text-gray-900 bg-white placeholder-gray-400
                                   focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        @error('name') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>


                    {{-- EMAIL --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" wire:model="email"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg
                                   text-gray-900 bg-white placeholder-gray-400
                                   focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        @error('email') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>


                    {{-- PASSWORD --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Password {{ $editMode ? '(leave blank to keep current)' : '' }}
                        </label>
                        <input type="password" wire:model="password"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg
                                   text-gray-900 bg-white placeholder-gray-400
                                   focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        @error('password') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>


                    {{-- STATUS --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select wire:model="status"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg
                                   text-gray-900 bg-white
                                   focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                        @error('status') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>


                    {{-- ACTIONS --}}
                    <div class="flex gap-3 pt-4">
                        <button type="submit"
                            class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                            {{ $editMode ? 'Update' : 'Create' }}
                        </button>

                        <button type="button" wire:click="closeModal"
                            class="flex-1 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                            Cancel
                        </button>
                    </div>

                </form>

            </div>
        </div>
    @endif
</div>