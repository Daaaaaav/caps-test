<div class="min-h-screen bg-gray-50" wire:poll.1000ms>
    @php
        use Carbon\Carbon;

        if (!function_exists('fmtDate')) {
            function fmtDate($v){
                try { return $v ? Carbon::parse($v)->format('d M Y') : '—'; }
                catch(\Throwable){ return '—'; }
            }
        }

        if (!function_exists('fmtTime')) {
            function fmtTime($v){
                try { return $v ? Carbon::parse($v)->format('H:i') : '—'; }
                catch(\Throwable){
                    if (is_string($v) && preg_match('/^\d{2}:\d{2}/',$v)) {
                        return substr($v,0,5);
                    }
                    return '—';
                }
            }
        }

        // Theme tokens
        $card   = 'bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden';
        $label  = 'block text-sm font-medium text-gray-700 mb-2';
        $input  = 'w-full h-10 px-3 rounded-lg border border-gray-300 text-gray-800 placeholder:text-gray-400 focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10 bg-white transition';
        $btnBlk = 'px-3 py-2 text-xs font-medium rounded-lg bg-gray-900 text-white hover:bg-black focus:outline-none focus:ring-2 focus:ring-gray-900/20 disabled:opacity-60 transition';
        $btnGrn = 'px-3 py-2 text-xs font-medium rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-600/20 disabled:opacity-60 transition';

        $chip      = 'inline-flex items-center gap-2 px-2.5 py-1 rounded-lg bg-gray-100 text-xs';
        $icoAvatar = 'w-10 h-10 bg-gray-900 rounded-xl flex items-center justify-center text-white font-semibold text-sm shrink-0';
        $editIn    = 'w-full h-10 bg-white border border-gray-300 rounded-lg px-3 text-gray-800 focus:border-gray-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-gray-900/10 transition hover:border-gray-400 placeholder:text-gray-400';
    @endphp

    <main class="px-4 sm:px-6 py-6 space-y-6">
        {{-- Flash Messages --}}
        @if (session('success') || session('error'))
            <div class="max-w-3xl mx-auto">
                @if (session('success'))
                    <div class="mb-2 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                        {{ session('success') }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="mb-2 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                        {{ session('error') }}
                    </div>
                @endif
            </div>
        @endif

        {{-- HERO --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-gray-900 to-black text-white shadow-2xl">
            <div class="pointer-events-none absolute inset-0 opacity-10">
                <div class="absolute top-0 -right-4 w-24 h-24 bg-white rounded-full blur-xl"></div>
                <div class="absolute bottom-0 -left-4 w-16 h-16 bg-white rounded-full blur-lg"></div>
            </div>
            <div class="relative z-10 p-6 sm:p-8">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm border border-white/20">
                            <x-heroicon-o-user-group class="w-6 h-6 text-white" />
                        </div>
                        <div>
                            <h1 class="text-lg sm:text-xl font-semibold">Guestbook</h1>
                            <p class="text-sm text-white/80">
                                Pantau kunjungan yang masih aktif dan riwayat kunjungan tamu.
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <label class="inline-flex items-center gap-2 text-sm text-white/90 cursor-pointer">
                            <input type="checkbox"
                                   wire:model.live="withTrashed"
                                   class="rounded border-white/30 bg-white/10 focus:ring-white/40">
                            <span>Include deleted</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        {{-- MAIN CARD WITH TABS (Riwayat / Terbaru) --}}
        <section class="{{ $card }}">
            {{-- Header: title + tabs + filters --}}
            <div class="px-4 sm:px-6 pt-4 pb-3 border-b border-gray-200 space-y-3">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h2 class="text-base font-semibold text-gray-900">Guestbook</h2>
                        <p class="text-xs text-gray-500">
                            Beralih antara riwayat kunjungan dan kunjungan terbaru yang masih aktif.
                        </p>
                    </div>

                    {{-- Segmented tabs --}}
                    <div class="inline-flex items-center bg-gray-100 rounded-full p-1 text-xs font-medium">
                        <button type="button"
                                wire:click="setTab('entries')"
                                class="px-3 py-1 rounded-full transition
                                    {{ $activeTab === 'entries'
                                        ? 'bg-gray-900 text-white shadow-sm'
                                        : 'text-gray-700 hover:bg-gray-200' }}">
                            Riwayat Kunjungan
                        </button>
                        <button type="button"
                                wire:click="setTab('latest')"
                                class="px-3 py-1 rounded-full transition
                                    {{ $activeTab === 'latest'
                                        ? 'bg-gray-900 text-white shadow-sm'
                                        : 'text-gray-700 hover:bg-gray-200' }}">
                            Kunjungan Terbaru
                        </button>
                    </div>
                </div>

                {{-- Filters (only for Riwayat Kunjungan) --}}
                @if($activeTab === 'entries')
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                        {{-- Search --}}
                        <div>
                            <label class="{{ $label }}">Search</label>
                            <div class="relative">
                                <input type="text"
                                       class="{{ $input }} pl-9"
                                       placeholder="Cari nama, no HP, instansi, petugas, keperluan…"
                                       wire:model.live="q">
                                <x-heroicon-o-magnifying-glass class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                            </div>
                        </div>

                        {{-- Date --}}
                        <div>
                            <label class="{{ $label }}">Tanggal</label>
                            <div class="relative">
                                <input type="date"
                                       class="{{ $input }} pl-9"
                                       wire:model.live="filter_date">
                                <x-heroicon-o-calendar-days class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                            </div>
                        </div>

                        {{-- Sort --}}
                        <div>
                            <label class="{{ $label }}">Urutkan</label>
                            <select class="{{ $input }}" wire:model.live="dateMode">
                                <option value="semua">Default (terbaru)</option>
                                <option value="terbaru">Tanggal terbaru</option>
                                <option value="terlama">Tanggal terlama</option>
                            </select>
                        </div>
                    </div>
                @else
                    <p class="mt-1 text-xs text-gray-500">
                        Menampilkan kunjungan hari ini yang belum mencatat jam keluar.
                    </p>
                @endif
            </div>

            {{-- LIST AREA: switch between entries & latest --}}
            <div class="p-4 sm:p-6">
                {{-- Riwayat Kunjungan --}}
                @if($activeTab === 'entries')
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        @forelse ($entries as $e)
                            @php
                                $rowNo    = ($entries->firstItem() ?? 1) + $loop->index;
                                $stateKey = $e->deleted_at ? 'trash' : 'ok';
                            @endphp

                            {{-- START: MODIFIED HISTORY CARD DESIGN (GUESTBOOK) --}}
                            <div class="bg-white border border-gray-200 rounded-xl p-4 space-y-3 hover:shadow-sm hover:border-gray-300 transition"
                                 wire:key="entry-{{ $e->guestbook_id }}-{{ $stateKey }}">
                                
                                <div class="flex items-start gap-4">
                                    {{-- 1. Avatar/Initial on the left --}}
                                    <div class="{{ $icoAvatar }} mt-0.5">
                                        {{ strtoupper(substr($e->name ?? '—', 0, 1)) }}
                                    </div>
                                    
                                    <div class="flex-1 min-w-0">
                                        {{-- 2. TOP ROW: Title, Phone, Status --}}
                                        <div class="flex items-center justify-between gap-3 min-w-0 mb-2">
                                            <h3 class="font-semibold text-gray-900 text-base truncate pr-2">
                                                {{ $e->name }}
                                            </h3>
                                            <div class="flex-shrink-0 flex items-center gap-2">
                                                @if ($e->phone_number)
                                                    <span class="text-[11px] px-2 py-0.5 rounded-md bg-gray-100 text-gray-700 flex-shrink-0">
                                                        {{ $e->phone_number }}
                                                    </span>
                                                @endif
                                                @if($e->deleted_at)
                                                    <span class="text-[11px] px-2 py-0.5 rounded-full bg-rose-100 text-rose-800 flex-shrink-0">
                                                        Deleted
                                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                        {{-- 3. MIDDLE SECTION: Instansi, Keperluan, Date/Time --}}
                                        <div class="space-y-2 text-[13px] text-gray-600 mb-3 border-y border-gray-100 py-2">
                                            
                                            {{-- Instansi & Keperluan Chips --}}
                                            <div class="flex flex-wrap gap-1.5">
                                                <span class="{{ $chip }} text-xs px-2.5 py-0.5">
                                                    <x-heroicon-o-building-office class="w-3.5 h-3.5 text-gray-500" />
                                                    <span class="font-medium text-gray-700">{{ $e->instansi ?? '—' }}</span>
                                                </span>
                                                <span class="{{ $chip }} text-xs px-2.5 py-0.5">
                                                    <x-heroicon-o-clipboard-document class="w-3.5 h-3.5 text-gray-500" />
                                                    <span class="font-medium text-gray-700">{{ $e->keperluan ?? '—' }}</span>
                                                </span>
                                            </div>

                                            {{-- Date, Clock-in & out, Petugas --}}
                                            <div class="flex flex-wrap items-center gap-4">
                                                <span class="flex items-center gap-1.5 font-medium text-gray-800">
                                                    <x-heroicon-o-calendar-days class="w-4 h-4 text-gray-500" />
                                                    {{ fmtDate($e->date) }}
                                                </span>

                                                <span class="flex items-center gap-1.5 font-medium text-gray-800">
                                                    <x-heroicon-o-clock class="w-4 h-4 text-emerald-600" />
                                                    {{ fmtTime($e->jam_in) }}
                                                    <span class="mx-1.5 text-gray-400">–</span>
                                                    <x-heroicon-o-clock class="w-4 h-4 text-rose-600" />
                                                    {{ fmtTime($e->jam_out) }}
                                                </span>
                                            </div>
                                            
                                            <span class="flex items-center gap-1.5 text-[13px] font-medium text-gray-700">
                                                <x-heroicon-o-user class="w-4 h-4 text-gray-500" />
                                                Petugas: {{ $e->petugas_penjaga }}
                                            </span>
                                        </div>
                                        
                                        {{-- 4. Timestamp --}}
                                        <span class="inline-block text-[10px] text-gray-500">
                                            Created: {{ \Carbon\Carbon::parse($e->created_at)->format('d M Y H:i') }}
                                        </span>
                                    </div>
                                </div>
                                
                                {{-- 5. BOTTOM ACTIONS (Horizontally aligned and right justified) --}}
                                <div class="pt-3 border-t border-gray-100 flex justify-end gap-3 items-center">
                                    <span class="inline-block text-[11px] px-2 py-0.5 rounded-lg bg-gray-100 text-gray-600 border border-gray-200 mr-auto">
                                        No. {{ $rowNo }}
                                    </span>
                                    
                                    <button wire:click="openEdit({{ $e->guestbook_id }})"
                                            wire:loading.attr="disabled"
                                            wire:target="openEdit({{ $e->guestbook_id }})"
                                            class="{{ $btnBlk }} px-4 py-2">
                                        <span wire:loading.remove wire:target="openEdit({{ $e->guestbook_id }})">
                                            Edit
                                        </span>
                                        <span wire:loading wire:target="openEdit({{ $e->guestbook_id }})">
                                            Memuat…
                                        </span>
                                    </button>

                                    @if(!$e->deleted_at)
                                        {{-- Soft delete --}}
                                        <button wire:click="delete({{ $e->guestbook_id }})"
                                                onclick="return confirm('Hapus entri ini?')"
                                                wire:loading.attr="disabled"
                                                wire:target="delete({{ $e->guestbook_id }})"
                                                class="px-4 py-2 text-xs font-medium rounded-lg bg-rose-600 text-white hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-600/20 disabled:opacity-60 transition">
                                            <span wire:loading.remove wire:target="delete({{ $e->guestbook_id }})">
                                                Hapus
                                            </span>
                                            <span wire:loading wire:target="delete({{ $e->guestbook_id }})">
                                                Menghapus…
                                            </span>
                                        </button>
                                    @else
                                        {{-- Restore --}}
                                        <button wire:click="restore({{ $e->guestbook_id }})"
                                                wire:loading.attr="disabled"
                                                wire:target="restore({{ $e->guestbook_id }})"
                                                class="px-4 py-2 text-xs font-medium rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-600/20 disabled:opacity-60 transition">
                                            <span wire:loading.remove wire:target="restore({{ $e->guestbook_id }})">
                                                Restore
                                            </span>
                                            <span wire:loading wire:target="restore({{ $e->guestbook_id }})">
                                                Memproses…
                                            </span>
                                        </button>

                                        {{-- Permanent delete --}}
                                        <button wire:click="destroyForever({{ $e->guestbook_id }})"
                                                onclick="return confirm('Hapus permanen entri ini? Tindakan tidak bisa dibatalkan!')"
                                                wire:loading.attr="disabled"
                                                wire:target="destroyForever({{ $e->guestbook_id }})"
                                                class="px-4 py-2 text-xs font-medium rounded-lg bg-rose-700 text-white hover:bg-rose-800 focus:outline-none focus:ring-2 focus:ring-rose-700/20 disabled:opacity-60 transition">
                                            <span wire:loading.remove wire:target="destroyForever({{ $e->guestbook_id }})">
                                                Hapus Permanen
                                            </span>
                                            <span wire:loading wire:target="destroyForever({{ $e->guestbook_id }})">
                                                Menghapus…
                                            </span>
                                        </button>
                                    @endif
                                </div>
                            </div>
                            {{-- END: MODIFIED HISTORY CARD DESIGN (GUESTBOOK) --}}
                        @empty
                            <div class="lg:col-span-2 py-14 text-center text-gray-500 text-sm">
                                Tidak ada entri kunjungan yang ditemukan
                            </div>
                        @endforelse
                    </div>

                {{-- Kunjungan Terbaru (Belum Keluar) --}}
                @else
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        @forelse ($latest as $r)
                            @php
                                $rowNoLatest = ($latest->firstItem() ?? 1) + $loop->index;
                            @endphp

                            {{-- START: MODIFIED LATEST CARD DESIGN (GUESTBOOK) --}}
                            <div class="bg-white border border-gray-200 rounded-xl p-4 space-y-3 hover:shadow-sm hover:border-gray-300 transition"
                                 wire:key="latest-{{ $r->guestbook_id }}">
                                
                                <div class="flex items-start gap-4">
                                    {{-- 1. Avatar/Initial on the left --}}
                                    <div class="{{ $icoAvatar }} mt-0.5">
                                        {{ strtoupper(substr($r->name ?? '—', 0, 1)) }}
                                    </div>
                                    
                                    <div class="flex-1 min-w-0">
                                        {{-- 2. TOP ROW: Title, Phone, Status --}}
                                        <div class="flex items-center justify-between gap-3 min-w-0 mb-2">
                                            <h4 class="font-semibold text-gray-900 text-base truncate pr-2">
                                                {{ $r->name }}
                                            </h4>
                                            <div class="flex-shrink-0 flex items-center gap-2">
                                                @if ($r->phone_number)
                                                    <span class="text-[11px] px-2 py-0.5 rounded-md bg-gray-100 text-gray-700 flex-shrink-0">
                                                        {{ $r->phone_number }}
                                                    </span>
                                                @endif
                                                <span class="text-[11px] px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 flex-shrink-0">
                                                    Aktif
                                                </span>
                                            </div>
                                        </div>

                                        {{-- 3. MIDDLE SECTION: Instansi, Keperluan, Date/Time --}}
                                        <div class="space-y-2 text-[13px] text-gray-600 mb-3 border-y border-gray-100 py-2">
                                            
                                            {{-- Instansi & Keperluan Chips --}}
                                            <div class="flex flex-wrap gap-1.5">
                                                <span class="{{ $chip }} text-xs px-2.5 py-0.5">
                                                    <x-heroicon-o-building-office class="w-3.5 h-3.5 text-gray-500" />
                                                    <span class="font-medium text-gray-700">{{ $r->instansi ?? '—' }}</span>
                                                </span>
                                                <span class="{{ $chip }} text-xs px-2.5 py-0.5">
                                                    <x-heroicon-o-clipboard-document class="w-3.5 h-3.5 text-gray-500" />
                                                    <span class="font-medium text-gray-700">{{ $r->keperluan ?? '—' }}</span>
                                                </span>
                                            </div>

                                            {{-- Date, Clock-in, Petugas --}}
                                            <div class="flex flex-wrap items-center gap-4">
                                                <span class="flex items-center gap-1.5 font-medium text-gray-800">
                                                    <x-heroicon-o-calendar-days class="w-4 h-4 text-gray-500" />
                                                    {{ fmtDate($r->date) }}
                                                </span>
                                                <span class="flex items-center gap-1.5 font-medium text-gray-800">
                                                    <x-heroicon-o-clock class="w-4 h-4 text-emerald-600" />
                                                    Masuk: {{ fmtTime($r->jam_in) }}
                                                </span>
                                            </div>
                                            
                                            <span class="flex items-center gap-1.5 text-[13px] font-medium text-gray-700">
                                                <x-heroicon-o-user class="w-4 h-4 text-gray-500" />
                                                Petugas: {{ $r->petugas_penjaga }}
                                            </span>
                                        </div>
                                        
                                        {{-- 4. Timestamp --}}
                                        @if(!empty($r->created_at))
                                            <span class="inline-block text-[10px] text-gray-500">
                                                Created: {{ \Carbon\Carbon::parse($r->created_at)->format('d M Y H:i') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                {{-- 5. BOTTOM ACTIONS (Horizontally aligned and right justified) --}}
                                <div class="pt-3 border-t border-gray-100 flex justify-end gap-3 items-center">
                                    <span class="inline-block text-[11px] px-2 py-0.5 rounded-lg bg-gray-100 text-gray-600 border border-gray-200 mr-auto">
                                        No. {{ $rowNoLatest }}
                                    </span>

                                    <button
                                        wire:click="openEdit({{ $r->guestbook_id }})"
                                        wire:loading.attr="disabled"
                                        wire:target="openEdit({{ $r->guestbook_id }})"
                                        class="{{ $btnBlk }} px-4 py-2">
                                        <span wire:loading.remove wire:target="openEdit({{ $r->guestbook_id }})">
                                            Edit
                                        </span>
                                        <span wire:loading wire:target="openEdit({{ $r->guestbook_id }})">
                                            Memuat…
                                        </span>
                                    </button>

                                    <button
                                        wire:click="setJamKeluarNow({{ $r->guestbook_id }})"
                                        wire:loading.attr="disabled"
                                        wire:target="setJamKeluarNow({{ $r->guestbook_id }})"
                                        class="{{ $btnGrn }} px-4 py-2">
                                        <span wire:loading.remove wire:target="setJamKeluarNow({{ $r->guestbook_id }})">
                                            Keluar sekarang
                                        </span>
                                        <span wire:loading wire:target="setJamKeluarNow({{ $r->guestbook_id }})">
                                            Menyimpan…
                                        </span>
                                    </button>
                                </div>
                            </div>
                            {{-- END: MODIFIED LATEST CARD DESIGN (GUESTBOOK) --}}
                        @empty
                            <div class="lg:col-span-2 py-14 text-center text-gray-500 text-sm">
                                Belum ada kunjungan aktif hari ini
                            </div>
                        @endforelse
                    </div>
                @endif
            </div>

            {{-- Pagination (switch based on active tab) --}}
            <div class="px-4 sm:px-6 py-5 bg-gray-50 border-t border-gray-200">
                <div class="flex justify-center">
                    @if($activeTab === 'entries')
                        {{ $entries->onEachSide(1)->links() }}
                    @else
                        {{ $latest->onEachSide(1)->links() }}
                    @endif
                </div>
            </div>
        </section>

        {{-- EDIT MODAL (No changes needed here as it's separate from the list design) --}}
        @if ($showEdit)
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-all duration-300"
                     wire:click="closeEdit"></div>

                <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden transform transition-all duration-300 max-h-[90vh] flex flex-col">
                    <div class="bg-gradient-to-r from-gray-900 to-black p-5 text-white relative overflow-hidden">
                        <div class="absolute inset-0 opacity-10 pointer-events-none">
                            <div class="absolute top-0 -right-4 w-12 h-12 bg-white rounded-full blur-sm"></div>
                            <div class="absolute bottom-0 -left-4 w-10 h-10 bg-white rounded-full blur-sm"></div>
                        </div>
                        <h3 class="text-lg font-semibold relative z-10">
                            Edit Entri Kunjungan
                        </h3>
                    </div>

                    <div class="p-6 overflow-y-auto flex-1">
                        <form wire:submit.prevent="saveEdit">
                            <div class="grid grid-cols-1 gap-6">
                                {{-- Nama --}}
                                <div>
                                    <label for="edit_name" class="{{ $label }}">Nama Tamu <span class="text-rose-500">*</span></label>
                                    <input type="text" id="edit_name" class="{{ $editIn }}" wire:model.defer="editForm.name">
                                    @error('editForm.name') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                                </div>

                                {{-- No HP --}}
                                <div>
                                    <label for="edit_phone_number" class="{{ $label }}">No. HP</label>
                                    <input type="text" id="edit_phone_number" class="{{ $editIn }}" wire:model.defer="editForm.phone_number">
                                    @error('editForm.phone_number') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                                </div>

                                {{-- Instansi --}}
                                <div>
                                    <label for="edit_instansi" class="{{ $label }}">Instansi</label>
                                    <input type="text" id="edit_instansi" class="{{ $editIn }}" wire:model.defer="editForm.instansi">
                                    @error('editForm.instansi') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                                </div>

                                {{-- Keperluan --}}
                                <div>
                                    <label for="edit_keperluan" class="{{ $label }}">Keperluan <span class="text-rose-500">*</span></label>
                                    <textarea id="edit_keperluan" class="{{ $editIn }} h-20 pt-2" wire:model.defer="editForm.keperluan"></textarea>
                                    @error('editForm.keperluan') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                                </div>

                                {{-- Petugas Penjaga --}}
                                <div>
                                    <label for="edit_petugas_penjaga" class="{{ $label }}">Petugas Penjaga <span class="text-rose-500">*</span></label>
                                    <input type="text" id="edit_petugas_penjaga" class="{{ $editIn }}" wire:model.defer="editForm.petugas_penjaga">
                                    @error('editForm.petugas_penjaga') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                                </div>

                                {{-- Date / Jam In / Jam Out --}}
                                <div class="grid grid-cols-3 gap-4">
                                    <div>
                                        <label for="edit_date" class="{{ $label }}">Tanggal <span class="text-rose-500">*</span></label>
                                        <input type="date" id="edit_date" class="{{ $editIn }}" wire:model.defer="editForm.date">
                                        @error('editForm.date') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                                    </div>

                                    <div>
                                        <label for="edit_jam_in" class="{{ $label }}">Jam Masuk <span class="text-rose-500">*</span></label>
                                        <input type="time" id="edit_jam_in" class="{{ $editIn }}" wire:model.defer="editForm.jam_in">
                                        @error('editForm.jam_in') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                                    </div>

                                    <div>
                                        <label for="edit_jam_out" class="{{ $label }}">Jam Keluar</label>
                                        <input type="time" id="edit_jam_out" class="{{ $editIn }}" wire:model.defer="editForm.jam_out">
                                        @error('editForm.jam_out') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="mt-8 pt-4 border-t border-gray-200 flex justify-end gap-3">
                                <button type="button" class="px-3 py-2 text-xs font-medium rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 transition" wire:click="closeEdit">
                                    Batal
                                </button>
                                <button type="submit" class="{{ $btnBlk }}" wire:loading.attr="disabled" wire:target="saveEdit">
                                    <span wire:loading.remove wire:target="saveEdit">Simpan Perubahan</span>
                                    <span wire:loading wire:target="saveEdit">Menyimpan…</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    </main>
</div>