<div class="bg-gray-50">
    {{-- Style Variables (from department page) --}}
    @php
        $card = 'bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden';
        $label = 'block text-sm font-medium text-gray-700 mb-2';
        $input = 'w-full h-10 px-3 rounded-lg border border-gray-300 text-gray-800 placeholder:text-gray-400 focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10 bg-white transition';
        $btnBlk = 'px-3 py-2 text-xs font-medium rounded-lg bg-gray-900 text-white hover:bg-black focus:outline-none focus:ring-2 focus:ring-gray-900/20 disabled:opacity-60 transition';
        $btnRed = 'px-3 py-2 text-xs font-medium rounded-lg bg-rose-600 text-white hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-600/20 disabled:opacity-60 transition';
        $chip = 'inline-flex items-center gap-2 px-2.5 py-1 rounded-lg bg-gray-100 text-xs';
        $mono = 'text-[10px] font-mono text-gray-500 bg-gray-100 px-2.5 py-1 rounded-md';
        $ico = 'w-10 h-10 bg-gray-900 rounded-xl flex items-center justify-center text-white font-semibold text-sm shrink-0';
    @endphp

    <main class="px-4 sm:px-6 py-6 space-y-8">
        {{-- HERO --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-gray-900 to-black text-white shadow-2xl">
            <div class="pointer-events-none absolute inset-0 opacity-10">
                <div class="absolute top-0 -right-4 w-24 h-24 bg-white rounded-full blur-xl"></div>
                <div class="absolute bottom-0 -left-4 w-16 h-16 bg-white rounded-full blur-lg"></div>
            </div>
            <div class="relative z-10 p-6 sm:p-8">
                <div class="flex items-center gap-4">
                    <div
                        class="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm border border-white/20">
                        <x-heroicon-o-megaphone class="w-6 h-6 text-white" />
                    </div>
                    <div>
                        <h2 class="text-lg sm:text-xl font-semibold">Announcement Management</h2>
                        <p class="text-sm text-white/80">
                            Cabang: <span
                                class="font-semibold">{{ optional(Auth::user()->company)->company_name ?? '-' }}</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- CREATE FORM (always visible) --}}
        <section class="{{ $card }}">
            <div class="px-5 py-4 border-b border-gray-200">
                <h3 class="text-base font-semibold text-gray-900">Add New Announcement</h3>
            </div>
            <form class="p-5" wire:submit.prevent="store">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div class="md:col-span-1">
                        <label class="{{ $label }}">Company</label>
                        <input type="text" class="{{ $input }}"
                            value="{{ optional(Auth::user()->company)->company_name ?? '-' }}" readonly>
                    </div>
                    <div class="md:col-span-2">
                        <label class="{{ $label }}">Description</label>
                        <input type="text" wire:model.defer="description" class="{{ $input }}"
                            placeholder="e.g. Company wide meeting next Monday...">
                        @error('description') <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="md:col-span-1">
                        <label class="{{ $label }}">Event Date (Optional)</label>
                        <input type="datetime-local" wire:model.defer="event_at" class="{{ $input }}">
                        @error('event_at') <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div class="pt-5">
                    <button type="submit" wire:loading.attr="disabled" wire:target="store"
                        class="inline-flex items-center gap-2 px-5 h-10 rounded-xl bg-gray-900 text-white text-sm font-medium shadow-sm focus:outline-none focus:ring-2 focus:ring-gray-900/20 transition active:scale-95 hover:bg-black disabled:opacity-60 relative overflow-hidden">
                        <span class="flex items-center gap-2" wire:loading.remove wire:target="store">
                            <x-heroicon-o-check class="w-4 h-4" />
                            Save Announcement
                        </span>
                        <span class="flex items-center gap-2" wire:loading wire:target="store">
                            <x-heroicon-o-arrow-path class="h-4 w-4 animate-spin" />
                            Saving...
                        </span>
                    </button>
                </div>
            </form>
        </section>

        {{-- ANNOUNCEMENT LIST --}}
        <div class="{{ $card }}">
            <div class="px-5 py-4 border-b border-gray-200">
                <div class="relative flex-1">
                    <input type="text" wire:model.live="search" placeholder="Search announcements..."
                        class="{{ $input }} pl-10 w-full placeholder:text-gray-400">
                    <x-heroicon-o-magnifying-glass class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                </div>
            </div>
            <div class="divide-y divide-gray-200">
                @forelse ($announcements as $announcement)
                    @php
                        $rowNo = (($announcements->firstItem() ?? 1) + $loop->index);
                    @endphp

                    <div class="px-5 py-5 hover:bg-gray-50 transition-colors"
                        wire:key="announcement-{{ $announcement->announcements_id }}">
                        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                            <div class="flex items-start gap-3 flex-1">
                                <div class="{{ $ico }}">
                                    {{ substr(optional($announcement->company)->company_name ?? 'C', 0, 1) }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h4 class="font-semibold text-gray-900 text-sm sm:text-base">
                                        {{ $announcement->description }}
                                    </h4>
                                    <div class="flex flex-wrap items-center gap-2 mt-2">
                                        @if($announcement->event_at)
                                            <span class="{{ $chip }}">
                                                <x-heroicon-o-calendar class="w-3.5 h-3.5 text-gray-500" />
                                                <span
                                                    class="font-medium text-gray-700">{{ $announcement->formatted_event_date }}</span>
                                            </span>
                                        @endif
                                        <span class="{{ $chip }}">
                                            <span class="text-gray-500">Created:</span>
                                            <span
                                                class="font-medium text-gray-700">{{ $announcement->formatted_created_date }}</span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right shrink-0 space-y-2">
                                <div class="{{ $mono }}">No. {{ $rowNo }}</div>
                                <div class="flex flex-wrap gap-2 justify-end pt-1">
                                    <button wire:click="openEdit({{ $announcement->announcements_id }})"
                                        class="{{ $btnBlk }}" wire:loading.attr="disabled"
                                        wire:target="openEdit({{ $announcement->announcements_id }})"
                                        wire:key="btn-edit-ann-{{ $announcement->announcements_id }}">
                                        <span wire:loading.remove
                                            wire:target="openEdit({{ $announcement->announcements_id }})">Edit</span>
                                        <span wire:loading
                                            wire:target="openEdit({{ $announcement->announcements_id }})">Loading…</span>
                                    </button>

                                    <button wire:click="delete({{ $announcement->announcements_id }})"
                                        onclick="return confirm('Are you sure you want to delete this announcement?')"
                                        class="{{ $btnRed }}" wire:loading.attr="disabled"
                                        wire:target="delete({{ $announcement->announcements_id }})"
                                        wire:key="btn-del-ann-{{ $announcement->announcements_id }}">
                                        <span wire:loading.remove
                                            wire:target="delete({{ $announcement->announcements_id }})">Delete</span>
                                        <span wire:loading
                                            wire:target="delete({{ $announcement->announcements_id }})">Deleting…</span>
                                    </button>

                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-14 text-center text-gray-500 text-sm">No announcements found.</div>
                @endforelse
            </div>
            @if($announcements->hasPages())
                <div class="px-5 py-4 bg-gray-50 border-t border-gray-200">
                    <div class="flex justify-center">
                        {{ $announcements->links() }}
                    </div>
                </div>
            @endif
        </div>

        {{-- EDIT MODAL --}}
        @if($modalEdit)
            <div class="fixed inset-0 z-[60] flex items-center justify-center" role="dialog" aria-modal="true"
                wire:key="modal-edit-announcement" wire:keydown.escape.window="closeEdit">
                <button type="button" class="absolute inset-0 bg-black/50" aria-label="Close overlay"
                    wire:click="closeEdit"></button>
                <div class="relative w-full max-w-xl mx-4 {{ $card }} focus:outline-none" tabindex="-1">
                    <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                        <h3 class="text-base font-semibold text-gray-900">Edit Announcement</h3>
                        <button class="text-gray-500 hover:text-gray-700" type="button" wire:click="closeEdit"
                            aria-label="Close">
                            <x-heroicon-o-x-mark class="w-5 h-5" />
                        </button>
                    </div>
                    <form class="p-5" wire:submit.prevent="update">
                        <div class="space-y-5">
                            <div>
                                <label class="{{ $label }}">📝 Description</label>
                                <input type="text" class="{{ $input }}" wire:model.defer="edit_description" autofocus>
                                @error('edit_description') <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}
                                </p> @enderror
                            </div>
                            <div>
                                <label class="{{ $label }}">🗓️ Event Date (Optional)</label>
                                <input type="datetime-local" class="{{ $input }}" wire:model.defer="edit_event_at">
                                @error('edit_event_at') <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div class="mt-6 flex items-center justify-end gap-3 border-t border-gray-200 pt-4">
                            <button type="button"
                                class="px-4 h-10 rounded-xl border border-gray-300 text-gray-700 text-sm font-medium hover:bg-gray-100 hover:border-gray-400 transition"
                                wire:click="closeEdit">Cancel</button>
                            <button type="submit" wire:loading.attr="disabled" wire:target="update"
                                class="inline-flex items-center gap-2 px-5 h-10 rounded-xl bg-gray-900 text-white text-sm font-medium shadow-sm focus:outline-none focus:ring-2 focus:ring-gray-900/20 transition active:scale-95 hover:bg-black disabled:opacity-60">
                                <span class="flex items-center gap-2" wire:loading.remove wire:target="update">
                                    <x-heroicon-o-check class="w-4 h-4" />
                                    Save Changes
                                </span>
                                <span class="flex items-center gap-2" wire:loading wire:target="update">
                                    <x-heroicon-o-arrow-path class="h-4 w-4 animate-spin" />
                                    Saving...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </main>
</div>