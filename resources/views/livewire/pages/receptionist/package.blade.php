<div class="min-h-screen bg-gray-50" wire:poll.1000ms.keep-alive>
    @php
        $card   = 'bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden';
        $head   = 'bg-gradient-to-r from-gray-900 to-black';
        $hpad   = 'px-6 py-5';
        $tag    = 'w-1.5 bg-white rounded-full';
        $label  = 'block text-sm font-medium text-gray-700 mb-2';
        $input  = 'w-full h-10 px-3 rounded-lg border border-gray-300 text-gray-800 placeholder:text-gray-400 focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10 bg-white transition';
        $btnBlk = 'px-3 py-2 text-xs font-medium rounded-lg bg-gray-900 text-white hover:bg-black focus:outline-none focus:ring-2 focus:ring-gray-900/20 disabled:opacity-60 transition';
        $btnGrn = 'px-3 py-2 text-xs font-medium rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-600/20 disabled:opacity-60 transition';
        $btnAmb = 'px-3 py-2 text-xs font-medium rounded-lg bg-amber-600 text-white hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-600/20 disabled:opacity-60 transition';
        $btnRed = 'px-3 py-2 text-xs font-medium rounded-lg bg-rose-600 text-white hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-600/20 disabled:opacity-60 transition';
        $chip   = 'inline-flex items-center gap-2 px-2.5 py-1 rounded-lg bg-gray-100 text-xs';
        $mono   = 'text-[10px] font-mono text-gray-500 bg-gray-100 px-2.5 py-1 rounded-md';
        $icoAvatar = 'w-10 h-10 bg-gray-900 rounded-xl flex items-center justify-center text-white font-semibold text-sm shrink-0';
        $editIn = 'w-full h-10 bg-white border border-gray-300 rounded-lg px-3 text-gray-800 focus:border-gray-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-gray-900/10 transition hover:border-gray-400 placeholder:text-gray-400';
    @endphp

    <div class="px-4 sm:px-6 py-6 space-y-8">
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-gray-900 to-black text-white shadow-2xl">
            <div class="pointer-events-none absolute inset-0 opacity-10">
                <div class="absolute top-0 -right-4 w-24 h-24 bg-white rounded-full blur-xl"></div>
                <div class="absolute bottom-0 -left-4 w-16 h-16 bg-white rounded-full blur-lg"></div>
            </div>
            <div class="relative z-10 p-6 sm:p-8">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm border border-white/20">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v8m-4-4h8M4 6h16v12H4z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg sm:text-xl font-semibold">Packages</h2>
                        <p class="text-sm text-white/80">Kelola paket masuk (stored) & pengambilan (taken)</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- FORM TAMBAH/EDIT --}}
        <div class="{{ $card }}">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center gap-3">
                    <div class="w-2 h-2 bg-gray-900 rounded-full"></div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">Tambah Package</h3>
                        <p class="text-sm text-gray-500">Lengkapi data paket baru</p>
                    </div>
                </div>
            </div>

            <form wire:submit.prevent="save" class="p-6 space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="{{ $label }}">Nama Paket</label>
                        <input type="text" wire:model.defer="form.package_name" class="{{ $input }}" placeholder="Contoh: Paket Dokumen PT ABC">
                        @error('form.package_name') <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="{{ $label }}">Penyimpanan</label>
                        {{-- NOTE: pakai angka biar cocok storage_id (bigint). Ganti dengan data dari tabel storages kalau sudah ada. --}}
                        <select wire:model.defer="form.penyimpanan" class="{{ $input }}">
                            <option value="">-</option>
                            <option value="1">Rak 1</option>
                            <option value="2">Rak 2</option>
                            <option value="3">Rak 3</option>
                        </select>
                        @error('form.penyimpanan') <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="{{ $label }}">Expedition / Sender</label>
                        <input type="text" wire:model.defer="form.nama_pengirim" class="{{ $input }}" placeholder="Kurir / Pengirim">
                        @error('form.nama_pengirim') <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="{{ $label }}">Owner (Penerima)</label>
                        <input type="text" wire:model.defer="form.nama_penerima" class="{{ $input }}" placeholder="Nama penerima">
                        @error('form.nama_penerima') <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="pt-2 flex items-center gap-3">
                    <button type="submit" wire:loading.attr="disabled" wire:target="save"
                        class="inline-flex items-center gap-2 px-4 h-10 rounded-lg bg-gray-900 text-white text-sm font-medium shadow-sm focus:outline-none focus:ring-2 focus:ring-gray-900/20 transition active:scale-95 hover:bg-black disabled:opacity-60 relative overflow-hidden">
                        <span class="flex items-center gap-2" wire:loading.remove wire:target="save">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Simpan Data
                        </span>
                        <span class="flex items-center gap-2" wire:loading wire:target="save">
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

        {{-- ON-GOING (stored) --}}
        <div class="{{ $card }}">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center gap-3">
                    <div class="w-2 h-2 bg-amber-500 rounded-full"></div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">On-going Packages</h3>
                        <p class="text-sm text-gray-500">Menampilkan semua paket berstatus <b>stored</b></p>
                    </div>
                </div>
            </div>

            <div class="p-5 space-y-3">
                @forelse ($ongoing as $r)
                    <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 border border-gray-200"
                         wire:key="ongoing-{{ $r->delivery_id }}">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-9 h-9 bg-gray-900 rounded-lg flex items-center justify-center text-white text-xs font-semibold">
                                {{ strtoupper(substr($r->package_name, 0, 1)) }}
                            </div>
                            <div class="leading-tight min-w-0">
                                <div class="font-medium text-gray-800 text-sm truncate">{{ $r->package_name }}</div>
                                <div class="text-[11px] text-gray-500">
                                    Stored {{ optional($r->created_at)->format('d M Y H:i') ?? '—' }}
                                    • Container {{ $r->penyimpanan ?? '—' }}
                                    • Recep. {{ $r->receptionist->full_name ?? '—' }}
                                    • Sender {{ $r->nama_pengirim ?? '—' }}
                                    • Owner {{ $r->nama_penerima ?? '—' }}
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <button wire:click="openEdit({{ $r->delivery_id }})" wire:loading.attr="disabled"
                                    wire:target="openEdit({{ $r->delivery_id }})" class="{{ $btnBlk }}">
                                <span wire:loading.remove wire:target="openEdit({{ $r->delivery_id }})">Edit</span>
                                <span wire:loading wire:target="openEdit({{ $r->delivery_id }})">Memuat…</span>
                            </button>

                            <button wire:click="markDone({{ $r->delivery_id }})" wire:loading.attr="disabled"
                                    wire:target="markDone({{ $r->delivery_id }})" class="{{ $btnGrn }}">
                                <span class="inline-flex items-center gap-2">
                                    <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" wire:loading wire:target="markDone({{ $r->delivery_id }})">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0A12 12 0 000 12h4z" />
                                    </svg>
                                    <span>Done</span>
                                </span>
                            </button>

                            <button wire:click="delete({{ $r->delivery_id }})" onclick="return confirm('Hapus paket ini?')"
                                    wire:loading.attr="disabled" wire:target="delete({{ $r->delivery_id }})" class="{{ $btnRed }}">
                                <span wire:loading.remove wire:target="delete({{ $r->delivery_id }})">Hapus</span>
                                <span wire:loading wire:target="delete({{ $r->delivery_id }})">Menghapus…</span>
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-gray-500 text-sm">Tidak ada paket on-going.</div>
                @endforelse
            </div>

            <div class="px-6 py-5 bg-gray-50 border-t border-gray-200">
                <div class="flex justify-center">
                    {{ $ongoing->onEachSide(1)->links() }}
                </div>
            </div>
        </div>

        {{-- COMPLETED (taken) --}}
        <div class="{{ $card }}">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center gap-3">
                    <div class="w-2 h-2 bg-emerald-600 rounded-full"></div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">Completed Packages</h3>
                        <p class="text-sm text-gray-500">Paket berstatus <b>taken</b></p>
                    </div>
                </div>
            </div>

            <div class="divide-y divide-gray-200">
                @forelse ($done as $e)
                    @php $rowNo = (($done->firstItem() ?? 1) + $loop->index); @endphp

                    <div class="px-6 py-5 hover:bg-gray-50 transition-colors" wire:key="done-{{ $e->delivery_id }}">
                        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                            <div class="flex items-start gap-3 flex-1 min-w-0">
                                <div class="{{ $icoAvatar }}">{{ strtoupper(substr($e->package_name, 0, 1)) }}</div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2 mb-1.5">
                                        <h4 class="font-semibold text-gray-900 text-base truncate">{{ $e->package_name }}</h4>
                                        @if ($e->nama_pengirim)
                                            <span class="text-[11px] text-gray-600 bg-gray-100 px-2 py-0.5 rounded-md">Dari: {{ $e->nama_pengirim }}</span>
                                        @endif
                                        @if ($e->nama_penerima)
                                            <span class="text-[11px] text-gray-600 bg-gray-100 px-2 py-0.5 rounded-md">Ke: {{ $e->nama_penerima }}</span>
                                        @endif
                                    </div>

                                    <div class="flex flex-wrap gap-1.5 mb-2">
                                        <span class="{{ $chip }}">
                                            <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                            </svg>
                                            <span class="font-medium text-gray-700">{{ $e->penyimpanan ?? '—' }}</span>
                                        </span>
                                        <span class="{{ $chip }}">
                                            <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                            <span class="font-medium text-gray-700">{{ $e->receptionist->full_name ?? '—' }}</span>
                                        </span>
                                    </div>

                                    <div class="flex flex-wrap items-center gap-4 text-[13px] text-gray-600">
                                        <span class="flex items-center gap-1.5">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            Stored: {{ optional($e->created_at)->format('d M Y H:i') ?? '—' }}
                                        </span>
                                        <span class="flex items-center gap-1.5">
                                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                            </svg>
                                            Taken: {{ optional($e->pengambilan)->format('d M Y H:i') ?? '—' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="text-right shrink-0 space-y-2">
                                <div class="{{ $mono }}">No. {{ $rowNo }}</div>
                                <div class="text:[11px] text-gray-500">{{ optional($e->created_at)->format('d M Y H:i') }}</div>
                                <div class="flex flex-wrap gap-2 justify-end pt-1.5">
                                    <button wire:click="openEdit({{ $e->delivery_id }})" wire:loading.attr="disabled"
                                            wire:target="openEdit({{ $e->delivery_id }})" class="{{ $btnBlk }}">
                                        <span wire:loading.remove wire:target="openEdit({{ $e->delivery_id }})">Edit</span>
                                        <span wire:loading wire:target="openEdit({{ $e->delivery_id }})">Memuat…</span>
                                    </button>

                                    <button wire:click="markStored({{ $e->delivery_id }})" wire:loading.attr="disabled"
                                            wire:target="markStored({{ $e->delivery_id }})" class="{{ $btnAmb }}">
                                        <span class="inline-flex items-center gap-2">
                                            <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" wire:loading wire:target="markStored({{ $e->delivery_id }})">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0A12 12 0 000 12h4z" />
                                            </svg>
                                            <span>Move to On-going</span>
                                        </span>
                                    </button>

                                    <button wire:click="delete({{ $e->delivery_id }})" onclick="return confirm('Hapus paket ini?')"
                                            wire:loading.attr="disabled" wire:target="delete({{ $e->delivery_id }})" class="{{ $btnRed }}">
                                        <span wire:loading.remove wire:target="delete({{ $e->delivery_id }})">Hapus</span>
                                        <span wire:loading wire:target="delete({{ $e->delivery_id }})">Menghapus…</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-14 text-center text-gray-500 text-sm">Tidak ada paket selesai.</div>
                @endforelse
            </div>

            <div class="px-6 py-5 bg-gray-50 border-t border-gray-200">
                <div class="flex justify-center">
                    {{ $done->onEachSide(1)->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
