{{-- resources/views/livewire/pages/superadmin/department.blade.php --}}
<div class="bg-gray-50">
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
                    <div class="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm border border-white/20">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 7h18M3 12h18M3 17h18" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg sm:text-xl font-semibold">Department Management</h2>
                        <p class="text-sm text-white/80">
                            Cabang: <span
                                class="font-semibold">{{ optional(Auth::user()->company)->company_name ?? '-' }}</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <section class="{{ $card }}">
            <div class="px-5 py-4 border-b border-gray-200">
                <h3 class="text-base font-semibold text-gray-900">Tambah Department</h3>
            </div>

            <form class="p-5" wire:submit.prevent="store">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="md:col-span-2">
                        <label class="{{ $label }}">Nama Department</label>
                        <input type="text" class="{{ $input }}" placeholder="e.g. Human Resource"
                            wire:model.defer="department_name">
                        @error('department_name') <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="pt-5">
                    <button type="submit" wire:loading.attr="disabled" wire:target="store"
                        class="inline-flex items-center gap-2 px-5 h-10 rounded-xl bg-gray-900 text-white text-sm font-medium shadow-sm focus:outline-none focus:ring-2 focus:ring-gray-900/20 transition active:scale-95 hover:bg-black disabled:opacity-60 relative overflow-hidden"
                        wire:loading.class="opacity-80 cursor-wait">
                        <span class="flex items-center gap-2" wire:loading.remove wire:target="store">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Simpan Data
                        </span>
                        <span class="flex items-center gap-2" wire:loading wire:target="store">
                            <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0A12 12 0 000 12h4z" />
                            </svg>
                            Menyimpan…
                        </span>
                    </button>
                </div>
            </form>
        </section>

        <div class="{{ $card }}">
            {{-- Header --}}
            <div class="px-5 py-4 border-b border-gray-200">
                <div class="flex flex-col lg:flex-row lg:items-center gap-4">
                    <div class="relative flex-1">
                        <input type="text" wire:model.live="search" placeholder="Cari department…"
                            class="{{ $input }} pl-10 w-full placeholder:text-gray-400">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="m21 21-4.3-4.3M10 18a8 8 0 1 1 0-16 8 8 0 0 1 0 16z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="divide-y divide-gray-200">
                @forelse ($rows as $r)
                @php
                $rowNo = (($rows->firstItem() ?? 1) + $loop->index);
                @endphp
                <div class="px-5 py-5 hover:bg-gray-50 transition-colors" wire:key="dept-{{ $r->department_id }}">
                    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">

                        {{-- Left --}}
                        <div class="flex items-start gap-4 flex-1">
                            <div class="{{ $ico }}">{{ strtoupper(substr($r->department_name, 0, 1)) }}</div>
                            <div class="min-w-0 flex-1 space-y-2">
                                <h4 class="font-semibold text-gray-900 text-sm sm:text-base truncate">
                                    {{ $r->department_name }}
                                </h4>

                                <div class="flex flex-wrap gap-3">
                                    <span class="{{ $chip }}">
                                        <span class="text-gray-500">Created:</span>
                                        <span class="font-medium text-gray-700">{{ $r->created_at?->format('Y-m-d H:i') }}</span>
                                    </span>
                                    <span class="{{ $chip }}">
                                        <span class="text-gray-500">Updated:</span>
                                        <span class="font-medium text-gray-700">{{ $r->updated_at?->format('Y-m-d H:i') }}</span>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="text-right shrink-0 space-y-2">
                            <p class="{{ $mono }}">No. {{ $rowNo }}</p>
                            <div class="flex flex-wrap gap-2 justify-end">
                                <button
                                    class="{{ $btnBlk }}"
                                    wire:click="openEdit({{ $r->department_id }})"
                                    wire:loading.attr="disabled"
                                    wire:target="openEdit({{ $r->department_id }})">
                                    <span wire:loading.remove wire:target="openEdit({{ $r->department_id }})">Edit</span>
                                    <span wire:loading wire:target="openEdit({{ $r->department_id }})">Loading…</span>
                                </button>

                                <button
                                    class="{{ $btnRed }}"
                                    wire:click="delete({{ $r->department_id }})"
                                    onclick="return confirm('Hapus department ini?')"
                                    wire:loading.attr="disabled"
                                    wire:target="delete({{ $r->department_id }})">
                                    <span wire:loading.remove wire:target="delete({{ $r->department_id }})">Hapus</span>
                                    <span wire:loading wire:target="delete({{ $r->department_id }})">Menghapus…</span>
                                </button>

                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="px-5 py-14 text-center text-gray-500 text-sm">Tidak ada data.</div>
                @endforelse
            </div>

            {{-- Pagination --}}
            @if($rows->hasPages())
            <div class="px-5 py-4 bg-gray-50 border-t border-gray-200">
                <div class="flex justify-center">
                    {{ $rows->links() }}
                </div>
            </div>
            @endif
        </div>


        {{-- MODAL EDIT (tanpa Alpine, murni Livewire) --}}
        @if($modalEdit)
        <div
            class="fixed inset-0 z-[60] flex items-center justify-center"
            role="dialog" aria-modal="true"
            wire:key="modal-edit-dept"
            wire:keydown.escape.window="closeEdit">
            {{-- Overlay --}}
            <button type="button"
                class="absolute inset-0 bg-black/50"
                aria-label="Close overlay"
                wire:click="closeEdit"></button>

            {{-- Dialog --}}
            <div class="relative w-full max-w-xl mx-4 {{ $card }} focus:outline-none" tabindex="-1">
                <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-base font-semibold text-gray-900">Edit Department</h3>
                    <button class="text-gray-500 hover:text-gray-700" type="button"
                        wire:click="closeEdit" aria-label="Close">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form class="p-5" wire:submit.prevent="update">
                    <div class="grid grid-cols-1 gap-5">
                        <div>
                            <label class="{{ $label }}">Nama Department</label>
                            <input type="text" class="{{ $input }}" wire:model.defer="edit_department_name" autofocus>
                            @error('edit_department_name') <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-3 border-t border-gray-200 pt-4">
                        <button type="button"
                            class="px-4 h-10 rounded-xl border border-gray-300 text-gray-700 text-sm font-medium hover:bg-gray-100 hover:border-gray-400 transition"
                            wire:click="closeEdit">
                            Batal
                        </button>
                        <button type="submit"
                            wire:loading.attr="disabled"
                            wire:target="update"
                            class="inline-flex items-center gap-2 px-5 h-10 rounded-xl bg-gray-900 text-white text-sm font-medium shadow-sm focus:outline-none focus:ring-2 focus:ring-gray-900/20 transition active:scale-95 hover:bg-black disabled:opacity-60">
                            <span class="flex items-center gap-2" wire:loading.remove wire:target="update">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Simpan Perubahan
                            </span>
                            <span class="flex items-center gap-2" wire:loading wire:target="update">
                                <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0A12 12 0 000 12h4z" />
                                </svg>
                                Menyimpan…
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endif
    </main>
</div>