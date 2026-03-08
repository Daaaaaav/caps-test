<div class="min-h-screen bg-gray-50" wire:poll.1000ms.keep-alive>
    @php
        $card = 'bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden';
        $head = 'bg-gradient-to-r from-gray-900 to-black';
        $hpad = 'px-6 py-5';
        $tag = 'w-1.5 bg-white rounded-full';
        $label = 'block text-sm font-medium text-gray-700 mb-2';
        $input = 'w-full h-10 px-3 rounded-lg border border-gray-300 text-gray-800 placeholder:text-gray-400 focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10 bg-white transition';
        $btnBlk = 'px-3 py-2 text-xs font-medium rounded-lg bg-gray-900 text-white hover:bg-black focus:outline-none focus:ring-2 focus:ring-gray-900/20 disabled:opacity-60 transition';
        $btnGrn = 'px-3 py-2 text-xs font-medium rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-600/20 disabled:opacity-60 transition';
        $chip = 'inline-flex items-center gap-2 px-2.5 py-1 rounded-lg bg-gray-100 text-xs';
        $mono = 'text-[10px] font-mono text-gray-500 bg-gray-100 px-2.5 py-1 rounded-md';
        $icoAvatar = 'w-10 h-10 bg-gray-900 rounded-xl flex items-center justify-center text-white font-semibold text-sm shrink-0';
        $icoDot = 'h-6';
        $sectPad = 'px-6 py-5';
        $editIn = 'w-full h-10 bg-white border border-gray-300 rounded-lg px-3 text-gray-800 focus:border-gray-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-gray-900/10 transition hover:border-gray-400 placeholder:text-gray-400';
    @endphp

    <div class="px-4 sm:px-6 py-6 space-y-8">
        @if (session('saved'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-800 px-4 py-3 shadow-sm">
                <div class="flex items-center gap-2 text-sm font-medium">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Data dokumen berhasil disimpan.
                </div>
            </div>
        @endif

        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-gray-900 to-black text-white shadow-2xl">
            <div class="pointer-events-none absolute inset-0 opacity-10">
                <div class="absolute top-0 -right-4 w-24 h-24 bg-white rounded-full blur-xl"></div>
                <div class="absolute bottom-0 -left-4 w-16 h-16 bg-white rounded-full blur-lg"></div>
            </div>
            <div class="relative z-10 p-6 sm:p-8">
                <div class="flex items-center gap-4">
                    <div
                        class="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm border border-white/20">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v8m-4-4h8M4 6h16v12H4z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg sm:text-xl font-semibold">Dokumen</h2>
                        <p class="text-sm text-white/80">Kelola dokumen masuk & pengiriman hari ini</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="{{ $card }}">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center gap-3">
                    <div class="w-2 h-2 bg-gray-900 rounded-full"></div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">Tambah Dokumen</h3>
                        <p class="text-sm text-gray-500">Lengkapi data dokumen</p>
                    </div>
                </div>
            </div>

            <form wire:submit.prevent="save" class="p-6 space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="{{ $label }}">Nama Dokumen</label>
                        <input type="text" wire:model.defer="document_name" class="{{ $input }}"
                            placeholder="Contoh: Surat Perintah">
                        @error('document_name') <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="{{ $label }}">Type</label>
                        <select wire:model.defer="type" class="{{ $input }}">
                            <option value="document">Document</option>
                            <option value="invoice">Invoice</option>
                            <option value="etc">Etc</option>
                        </select>
                        @error('type') <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="{{ $label }}">Nama Pengirim</label>
                        <input type="text" wire:model.defer="nama_pengirim" class="{{ $input }}"
                            placeholder="Instansi/Orang pengirim">
                    </div>
                    <div>
                        <label class="{{ $label }}">Nama Penerima</label>
                        <input type="text" wire:model.defer="nama_penerima" class="{{ $input }}"
                            placeholder="Penerima internal">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="{{ $label }}">Penyimpanan</label>
                        <select wire:model.defer="penyimpanan" class="{{ $input }}">
                            <option value="">-</option>
                            <option value="rak1">Rak 1</option>
                            <option value="rak2">Rak 2</option>
                            <option value="rak3">Rak 3</option>
                        </select>
                    </div>

                    <div>
                        <label class="{{ $label }}">Tanggal & Jam Pengambilan</label>
                        <div class="flex items-center gap-2">
                            <input type="date" wire:model.defer="pengambilan_date" class="{{ $input }} w-40">
                            <input type="time" wire:model.defer="pengambilan_time" class="{{ $input }} w-36">
                        </div>
                        @error('pengambilan_date') <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                        @error('pengambilan_time') <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="{{ $label }}">Status</label>
                        <select wire:model.defer="status" class="{{ $input }}">
                            <option value="pending">Pending</option>
                            <option value="taken">Taken</option>
                            <option value="delivered">Delivered</option>
                        </select>
                        @error('status') <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p> @enderror
                        <p class="text-[11px] text-gray-500 mt-1">
                            Jika pilih <b>Delivered</b>, data langsung masuk ke kotak Riwayat (ditandai waktu sekarang).
                        </p>
                    </div>
                </div>

                <div class="pt-2 flex items-center gap-3">
                    <button type="submit" wire:loading.attr="disabled" wire:target="save"
                        class="inline-flex items-center gap-2 px-4 h-10 rounded-lg bg-gray-900 text-white text-sm font-medium shadow-sm focus:outline-none focus:ring-2 focus:ring-gray-900/20 transition active:scale-95 hover:bg-black disabled:opacity-60 relative overflow-hidden">
                        <span class="flex items-center gap-2" wire:loading.remove wire:target="save">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            Simpan Data
                        </span>
                        <span class="flex items-center gap-2" wire:loading wire:target="save">
                            <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4" />
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0A12 12 0 000 12h4z" />
                            </svg>
                            Menyimpan…
                        </span>
                    </button>

                    @if (session('saved'))
                        <span
                            class="inline-flex items-center gap-1.5 px-3 h-8 rounded-lg bg-emerald-100 text-emerald-700 text-xs font-medium">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Tersimpan!
                        </span>
                    @endif
                </div>
            </form>
        </div>
        
        <div class="{{ $card }}">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center gap-3">
                    <div class="w-2 h-2 bg-amber-500 rounded-full"></div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">Dokumen Pending</h3>
                        <p class="text-sm text-gray-500">Menampilkan semua dokumen berstatus pending</p>
                    </div>
                </div>
            </div>
            <div class="p-5 space-y-3">
                @forelse ($pendingList as $r)
                    <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 border border-gray-200"
                        wire:key="pending-{{ $r->document_id }}">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-9 h-9 bg-gray-900 rounded-lg flex items-center justify-center text-white text-xs font-semibold">
                                {{ strtoupper(substr($r->document_name, 0, 1)) }}
                            </div>
                            <div class="leading-tight">
                                <div class="font-medium text-gray-800 text-sm">{{ $r->document_name }}</div>
                                <div class="text-[11px] text-gray-500">
                                    Pengambilan {{ optional($r->pengambilan)->format('H:i') ?? '—' }} • Pengirim
                                    {{ $r->nama_pengirim ?? '—' }}
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button wire:click="openEdit({{ $r->document_id }})" wire:loading.attr="disabled"
                                wire:target="openEdit({{ $r->document_id }})" class="{{ $btnBlk }}">
                                <span wire:loading.remove wire:target="openEdit({{ $r->document_id }})">Edit</span>
                                <span wire:loading wire:target="openEdit({{ $r->document_id }})">Memuat…</span>
                            </button>

                            <button wire:click="setSudahDikirim({{ $r->document_id }})" wire:loading.attr="disabled"
                                wire:target="setSudahDikirim({{ $r->document_id }})" class="{{ $btnGrn }} relative">
                                <span class="inline-flex items-center gap-2">
                                    <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" wire:loading
                                        wire:target="setSudahDikirim({{ $r->document_id }})">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4" />
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0A12 12 0 000 12h4z" />
                                    </svg>
                                    <span>Sudah dikirim</span>
                                </span>
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-gray-500 text-sm">Tidak ada pending.</div>
                @endforelse
            </div>
        </div>

        <div class="{{ $card }}">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center gap-3">
                    <div class="w-2 h-2 bg-emerald-600 rounded-full"></div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">Riwayat Dokumen</h3>
                        <p class="text-sm text-gray-500">Hanya dokumen yang sudah dikirim</p>
                    </div>
                </div>
            </div>

            <div class="px-6 py-5 bg-gray-50 border-b border-gray-200">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
                    <div class="relative">
                        <input type="date" wire:model.live="filter_date" class="w-full lg:w-56 {{ $input }} pl-9">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div class="relative flex-1">
                        <input type="text" wire:model.live="q"
                            placeholder="Cari nama dokumen / pengirim / penerima / type / penyimpanan / status..."
                            class="{{ $input }} pl-9 w-full">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="m21 21-4.3-4.3M10 18a8 8 0 1 1 0-16 8 8 0 0 1 0 16z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="divide-y divide-gray-200">
                @forelse ($entries as $e)
                    @php
                        $rowNo = ($entries->firstItem() ?? 1) + $loop->index;
                    @endphp
                    <div class="px-6 py-5 hover:bg-gray-50 transition-colors" wire:key="entry-{{ $e->document_id }}">
                        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                            <div class="flex items-start gap-3 flex-1">
                                <div class="{{ $icoAvatar }}">{{ strtoupper(substr($e->document_name, 0, 1)) }}</div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2 mb-1.5">
                                        <h4 class="font-semibold text-gray-900 text-base">{{ $e->document_name }}</h4>
                                        @if ($e->nama_pengirim)
                                            <span class="text-[11px] text-gray-600 bg-gray-100 px-2 py-0.5 rounded-md">Dari:
                                                {{ $e->nama_pengirim }}</span>
                                        @endif
                                        @if ($e->nama_penerima)
                                            <span class="text-[11px] text-gray-600 bg-gray-100 px-2 py-0.5 rounded-md">Ke:
                                                {{ $e->nama_penerima }}</span>
                                        @endif
                                    </div>

                                    <div class="flex flex-wrap gap-1.5 mb-2">
                                        <span class="{{ $chip }}">
                                            <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                            </svg>
                                            <span class="font-medium text-gray-700">{{ ucfirst($e->type) }}</span>
                                        </span>
                                        <span class="{{ $chip }}">
                                            <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                            </svg>
                                            <span class="font-medium text-gray-700">{{ $e->penyimpanan ?? '—' }}</span>
                                        </span>
                                    </div>

                                    <div class="flex flex-wrap items-center gap-4 text-[13px] text-gray-600">
                                        <span class="flex items-center gap-1.5">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            {{ optional($e->pengambilan)->format('d M Y H:i') ?? '—' }}
                                        </span>
                                        <span class="flex items-center gap-1.5">
                                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                            </svg>
                                            Dikirim: {{ optional($e->pengiriman)->format('d M Y H:i') }}
                                        </span>
                                        <span class="flex items-center gap-1.5">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                            <span class="font-medium text-gray-700">Status: {{ ucfirst($e->status) }}</span>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="text-right shrink-0 space-y-2">
                                <div class="{{ $mono }}">No. {{ $rowNo }}</div>
                                <div class="text:[11px] text-gray-500">{{ $e->created_at->format('d M Y H:i') }}</div>
                                <div class="flex flex-wrap gap-2 justify-end pt-1.5">
                                    <button wire:click="openEdit({{ $e->document_id }})" wire:loading.attr="disabled"
                                        wire:target="openEdit({{ $e->document_id }})" class="{{ $btnBlk }}">
                                        <span wire:loading.remove wire:target="openEdit({{ $e->document_id }})">Edit</span>
                                        <span wire:loading wire:target="openEdit({{ $e->document_id }})">Memuat…</span>
                                    </button>
                                    <button wire:click="delete({{ $e->document_id }})"
                                        onclick="return confirm('Hapus dokumen ini?')" wire:loading.attr="disabled"
                                        wire:target="delete({{ $e->document_id }})"
                                        class="px-3 py-2 text-xs font-medium rounded-lg bg-rose-600 text-white hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-600/20 disabled:opacity-60 transition">
                                        <span wire:loading.remove wire:target="delete({{ $e->document_id }})">Hapus</span>
                                        <span wire:loading wire:target="delete({{ $e->document_id }})">Menghapus…</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-14 text-center text-gray-500 text-sm">Tidak ada riwayat dokumen.</div>
                @endforelse
            </div>

            <div class="px-6 py-5 bg-gray-50 border-t border-gray-200">
                <div class="flex justify-center">
                    {{ $entries->onEachSide(1)->links() }}
                </div>
            </div>
        </div>
    </div>

    @if ($showEdit)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" wire:poll.1000ms>
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-all duration-300" wire:click="closeEdit">
            </div>
            <div
                class="relative w-full max-w-lg bg-white rounded-xl shadow-2xl border border-gray-200 overflow-hidden transform transition-all duration-300 scale-100 max-h-[90vh] flex flex-col">
                <div class="bg-gradient-to-r from-gray-900 to-black p-5 text-white relative overflow-hidden">
                    <div class="absolute inset-0 opacity-10 pointer-events-none">
                        <div class="absolute top-0 -right-6 w-24 h-24 bg-white rounded-full blur-2xl"></div>
                        <div class="absolute bottom-0 -left-4 w-16 h-16 bg-white rounded-full blur-xl"></div>
                    </div>
                    <div class="relative z-10 flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold tracking-tight">Edit Dokumen</h3>
                            <div class="flex items-center gap-2 mt-1">
                                <div class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse"></div>
                                <p class="text-[11px] text-gray-200 font-mono">{{ $this->serverClock }}</p>
                            </div>
                        </div>
                        <button
                            class="w-9 h-9 rounded-full bg-white/10 hover:bg-white/20 backdrop-blur-sm border border-white/20 flex items-center justify-center transition-all duration-200"
                            wire:click="closeEdit">
                            <svg class="w-4.5 h-4.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="p-5 space-y-4 overflow-y-auto flex-1">
                    <div class="space-y-1.5">
                        <label class="{{ $label }}">Nama Dokumen</label>
                        <input type="text" wire:model="edit.document_name" class="{{ $editIn }}" placeholder="Nama dokumen">
                        @error('edit.document_name') <p class="text-[11px] text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3.5">
                        <div class="space-y-1.5">
                            <label class="{{ $label }}">Type</label>
                            <select wire:model="edit.type" class="{{ $editIn }}">
                                <option value="document">Document</option>
                                <option value="invoice">Invoice</option>
                                <option value="etc">Etc</option>
                            </select>
                            @error('edit.type') <p class="text-[11px] text-red-600 font-medium">{{ $message }}</p> @enderror
                        </div>
                        <div class="space-y-1.5">
                            <label class="{{ $label }}">Penyimpanan</label>
                            <input type="text" wire:model="edit.penyimpanan" class="{{ $editIn }}" placeholder="Rak/Box">
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="{{ $label }}">Pengambilan</label>
                        <div class="flex items-center gap-2">
                            <input type="date" wire:model="edit.pengambilan_date" class="{{ $editIn }} w-40">
                            <input type="time" wire:model="edit.pengambilan_time" class="{{ $editIn }} w-36">
                        </div>
                        @error('edit.pengambilan_date') <p class="text-[11px] text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                        @error('edit.pengambilan_time') <p class="text-[11px] text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                        <div class="bg-gray-50 rounded-md p-3 border border-gray-200 mt-1.5">
                            <p class="text-[11px] text-gray-600 leading-relaxed">
                                💡 <span class="font-medium">Tips:</span> Klik <span
                                    class="font-semibold text-gray-900">Sudah dikirim</span> di daftar Pending/Taken untuk
                                pakai waktu real-time pengiriman.
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3.5">
                        <div class="space-y-1.5">
                            <label class="{{ $label }}">Pengiriman</label>
                            <input type="datetime-local" wire:model="edit.pengiriman" class="{{ $editIn }}">
                            @error('edit.pengiriman') <p class="text-[11px] text-red-600 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="space-y-1.5">
                            <label class="{{ $label }}">Nama Pengirim</label>
                            <input type="text" wire:model="edit.nama_pengirim" class="{{ $editIn }}" placeholder="Pengirim">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3.5">
                        <div class="space-y-1.5">
                            <label class="{{ $label }}">Nama Penerima</label>
                            <input type="text" wire:model="edit.nama_penerima" class="{{ $editIn }}" placeholder="Penerima">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3.5">
                        <div class="space-y-1.5">
                            <label class="{{ $label }}">Status</label>
                            <select wire:model="edit.status" class="{{ $editIn }}">
                                <option value="pending">Pending</option>
                                <option value="taken">Taken</option>
                                <option value="delivered">Delivered</option>
                            </select>
                            @error('edit.status') <p class="text-[11px] text-red-600 font-medium">{{ $message }}</p>
                            @enderror
                            <p class="text-[11px] text-gray-500 mt-1">
                                Akan otomatis menjadi <b>Delivered</b> bila <b>Pengiriman</b> diisi.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 border-t border-gray-200 p-5">
                    <div class="flex items-center justify-end gap-2.5">
                        <button type="button" wire:click="closeEdit"
                            class="px-4 h-10 rounded-lg border border-gray-300 text-gray-700 text-sm font-medium hover:bg-gray-100 hover:border-gray-400 transition">Batal</button>
                        <button type="button" wire:click="saveEdit" wire:loading.attr="disabled" wire:target="saveEdit"
                            class="px-4 h-10 rounded-lg bg-gray-900 text-white text-sm font-medium hover:bg-black focus:outline-none focus:ring-2 focus:ring-gray-900/20 disabled:opacity-60 transition shadow-sm">
                            <span wire:loading.remove wire:target="saveEdit" class="flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                Simpan Perubahan
                            </span>
                            <span wire:loading wire:target="saveEdit" class="flex items-center gap-2">
                                <svg class="animate-spin -ml-1 mr-1 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4" />
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                                </svg>
                                Menyimpan…
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>