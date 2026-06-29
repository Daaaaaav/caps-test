<div class="min-h-screen bg-gray-50" wire:poll.30s>
    @php
        use Carbon\Carbon;
        $card   = 'bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden';
        $label  = 'block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1.5';
        $input  = 'w-full h-10 px-3.5 rounded-lg border border-gray-300 bg-white text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-900/10 focus:border-gray-900 transition-all';
        $icoAvatar = 'w-10 h-10 rounded-xl flex items-center justify-center text-white font-semibold text-sm shrink-0';

        function gbsFmtTime($v) {
            if (!$v) return '—';
            try { return Carbon::parse($v)->format('H:i'); } catch (\Throwable $e) { return is_string($v) ? substr($v, 0, 5) : '—'; }
        }
        function gbsFmtDate($v) {
            if (!$v) return '—';
            try { return Carbon::parse($v)->format('d M Y'); } catch (\Throwable $e) { return '—'; }
        }
    @endphp

    <main class="px-4 sm:px-6 py-6 space-y-6">

        {{-- Hero Banner --}}
        <div class="relative overflow-hidden rounded-2xl bg-[#4A2F24] text-[#CDDEA7] shadow-2xl">
            <div class="pointer-events-none absolute inset-0 opacity-10">
                <div class="absolute top-0 -right-4 w-24 h-24 bg-[#CDDEA7] rounded-full blur-xl"></div>
                <div class="absolute bottom-0 -left-4 w-16 h-16 bg-[#CDDEA7] rounded-full blur-lg"></div>
            </div>
            <div class="relative z-10 p-6 sm:p-8">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-[#CDDEA7]/10 rounded-xl flex items-center justify-center border border-[#CDDEA7]/20">
                            <x-heroicon-o-qr-code class="w-6 h-6 text-[#CDDEA7]"/>
                        </div>
                        <div>
                            <h2 class="text-lg sm:text-xl font-semibold">Status Buku Tamu</h2>
                            <p class="text-xs text-[#CDDEA7]/75 mt-0.5">Tamu yang menunggu konfirmasi QR atau sedang berada di lokasi</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <a href="{{ route('receptionist.guestbook') }}"
                           class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-[#CDDEA7]/10 border border-[#CDDEA7]/20 text-xs font-semibold text-[#CDDEA7] hover:bg-[#CDDEA7]/20 transition">
                            <x-heroicon-o-plus class="w-3.5 h-3.5"/>
                            Tambah Tamu
                        </a>
                        <a href="{{ route('receptionist.guestbookhistory') }}"
                           class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-[#CDDEA7] border border-[#CDDEA7]/20 text-xs font-semibold text-[#4A2F24] hover:bg-[#CDDEA7]/90 transition">
                            <x-heroicon-o-clock class="w-3.5 h-3.5"/>
                            Riwayat
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Summary stat pills --}}
        <div class="flex flex-wrap gap-3">
            <div class="inline-flex items-center gap-2 px-4 py-2.5 bg-amber-50 border border-amber-200 rounded-xl text-sm font-semibold text-amber-800 shadow-sm">
                <span class="w-2.5 h-2.5 rounded-full bg-amber-400 animate-pulse inline-block"></span>
                {{ $pendingCount }} Menunggu Scan QR
            </div>
            <div class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-50 border border-blue-200 rounded-xl text-sm font-semibold text-blue-800 shadow-sm">
                <span class="w-2.5 h-2.5 rounded-full bg-blue-500 animate-pulse inline-block"></span>
                {{ $ongoingCount }} Sedang Berkunjung
            </div>
            <div class="inline-flex items-center gap-2 px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm font-semibold text-gray-600 shadow-sm">
                {{ $pendingCount + $ongoingCount }} Total Aktif
            </div>
        </div>

        {{-- Main area --}}
        <div class="{{ $card }}">

            {{-- Tabs + search --}}
            <div class="px-4 sm:px-6 pt-4 pb-3 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="inline-flex items-center bg-gray-100 rounded-full p-1 text-xs font-medium">
                    <button type="button" wire:click="setTab('pending')"
                        class="relative px-4 py-1.5 rounded-full transition
                            {{ $activeTab === 'pending' ? 'bg-amber-500 text-white shadow-sm' : 'text-gray-700 hover:bg-gray-200' }}">
                        &#9201; Menunggu Scan
                        @if($pendingCount > 0)
                            <span class="absolute -top-1 -right-1 w-4 h-4 rounded-full bg-amber-600 text-white text-[9px] flex items-center justify-center font-bold leading-none">
                                {{ $pendingCount > 9 ? '9+' : $pendingCount }}
                            </span>
                        @endif
                    </button>
                    <button type="button" wire:click="setTab('ongoing')"
                        class="relative px-4 py-1.5 rounded-full transition
                            {{ $activeTab === 'ongoing' ? 'bg-blue-500 text-white shadow-sm' : 'text-gray-700 hover:bg-gray-200' }}">
                        &#128203; Sedang Berkunjung
                        @if($ongoingCount > 0)
                            <span class="absolute -top-1 -right-1 w-4 h-4 rounded-full bg-blue-600 text-white text-[9px] flex items-center justify-center font-bold leading-none">
                                {{ $ongoingCount > 9 ? '9+' : $ongoingCount }}
                            </span>
                        @endif
                    </button>
                </div>

                <div class="flex items-center gap-2">
                    @if($petugasFilter)
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-[#4A2F24] text-[#CDDEA7] text-xs font-semibold">
                            <x-heroicon-o-user class="w-3 h-3"/>
                            {{ $petugasFilter }}
                            <button type="button" wire:click="clearPetugasFilter" class="ml-0.5 hover:text-white font-bold">×</button>
                        </span>
                    @endif
                    <div class="relative">
                        <input type="text" wire:model.live.debounce.300ms="q"
                            placeholder="Cari nama, instansi..."
                            class="h-9 pl-8 pr-3 rounded-lg border border-gray-300 bg-white text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-900/10 focus:border-gray-900 transition-all w-48 sm:w-56">
                        <x-heroicon-o-magnifying-glass class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400"/>
                    </div>
                </div>
            </div>

            {{-- Card grid --}}
            <div class="p-4 sm:p-6">

                {{-- ===== PENDING TAB ===== --}}
                @if($activeTab === 'pending')
                    @if($pendingEntries->isEmpty())
                        <div class="py-16 text-center text-gray-500 text-sm">
                            <div class="w-14 h-14 mx-auto mb-3 rounded-full bg-amber-50 border border-amber-100 flex items-center justify-center">
                                <x-heroicon-o-qr-code class="w-7 h-7 text-amber-400"/>
                            </div>
                            <p class="font-semibold text-gray-700">Tidak ada tamu yang menunggu</p>
                            <p class="text-xs text-gray-400 mt-1">Semua tamu sudah scan QR atau belum ada yang didaftarkan.</p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                            @foreach($pendingEntries as $e)
                                @php $avatarChar = strtoupper(substr($e->name ?? 'T', 0, 1)); @endphp
                                <div wire:key="pending-{{ $e->guestbook_id }}"
                                     class="bg-white border border-amber-200 rounded-xl p-4 flex flex-col gap-3 hover:shadow-md hover:border-amber-300 transition">
                                    {{-- Header row --}}
                                    <div class="flex items-start gap-3">
                                        <div class="{{ $icoAvatar }} bg-amber-500">{{ $avatarChar }}</div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between gap-2">
                                                <p class="font-semibold text-gray-900 truncate">{{ $e->name }}</p>
                                                <span class="shrink-0 inline-flex items-center gap-1 text-[10px] font-semibold px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 border border-amber-200">
                                                    &#9201; Menunggu
                                                </span>
                                            </div>
                                            @if($e->instansi)
                                                <p class="text-xs text-gray-500 mt-0.5 truncate">{{ $e->instansi }}</p>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Details --}}
                                    <div class="space-y-1 text-xs text-gray-600 bg-gray-50 rounded-lg p-2.5 border border-gray-100">
                                        @if($e->keperluan)
                                            <div class="flex gap-1.5"><span class="text-gray-400 shrink-0">Keperluan:</span><span class="font-medium text-gray-800 truncate">{{ $e->keperluan }}</span></div>
                                        @endif
                                        <div class="flex gap-1.5"><span class="text-gray-400 shrink-0">Masuk:</span><span class="font-semibold text-emerald-700">{{ gbsFmtDate($e->date) }} · {{ gbsFmtTime($e->jam_in) }}</span></div>
                                        @if($e->email)
                                            <div class="flex gap-1.5"><span class="text-gray-400 shrink-0">Email:</span><span class="font-medium text-gray-700 truncate">{{ $e->email }}</span></div>
                                        @endif
                                        <div class="flex gap-1.5"><span class="text-gray-400 shrink-0">Petugas:</span><span class="font-medium text-gray-700 truncate">{{ $e->petugas_penjaga }}</span></div>
                                    </div>

                                    {{-- QR info --}}
                                    @if($e->qr_token)
                                        <div class="flex items-center gap-2 text-[11px] text-gray-400 bg-amber-50 rounded-lg px-2.5 py-1.5 border border-amber-100">
                                            <x-heroicon-o-qr-code class="w-3.5 h-3.5 text-amber-500 shrink-0"/>
                                            <span class="truncate">QR dikirim. Belum ada scan.</span>
                                        </div>
                                    @else
                                        <div class="flex items-center gap-2 text-[11px] text-gray-400 bg-gray-50 rounded-lg px-2.5 py-1.5 border border-gray-100">
                                            <x-heroicon-o-envelope-open class="w-3.5 h-3.5 text-gray-400 shrink-0"/>
                                            <span>Tidak ada email — QR tidak dikirim</span>
                                        </div>
                                    @endif

                                    {{-- Actions --}}
                                    <div class="pt-2 border-t border-gray-100 flex items-center justify-end gap-1.5">
                                        <button wire:click="openEdit({{ $e->guestbook_id }})"
                                                class="px-2.5 py-1.5 text-xs font-semibold rounded-lg text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 transition focus:outline-none">
                                            Edit
                                        </button>
                                        @if($e->qr_token && $e->email)
                                            <button wire:click="resendQr({{ $e->guestbook_id }})"
                                                    wire:loading.attr="disabled"
                                                    class="px-2.5 py-1.5 text-xs font-semibold rounded-lg text-amber-700 bg-amber-50 border border-amber-200 hover:bg-amber-100 transition focus:outline-none">
                                                Kirim Ulang QR
                                            </button>
                                        @endif
                                        <button wire:click="checkOutNow({{ $e->guestbook_id }})"
                                                wire:confirm="Checkout tamu ini sekarang?"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-[#4E653D] text-white hover:bg-[#354C2B] transition shadow-sm focus:outline-none">
                                            <x-heroicon-o-arrow-right-start-on-rectangle class="w-3.5 h-3.5"/>
                                            Checkout
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-5">{{ $pendingEntries->links() }}</div>
                    @endif
                @endif

                {{-- ===== ONGOING TAB ===== --}}
                @if($activeTab === 'ongoing')
                    @if($ongoingEntries->isEmpty())
                        <div class="py-16 text-center text-gray-500 text-sm">
                            <div class="w-14 h-14 mx-auto mb-3 rounded-full bg-blue-50 border border-blue-100 flex items-center justify-center">
                                <x-heroicon-o-user-group class="w-7 h-7 text-blue-400"/>
                            </div>
                            <p class="font-semibold text-gray-700">Tidak ada tamu yang sedang berkunjung</p>
                            <p class="text-xs text-gray-400 mt-1">Tamu akan muncul di sini setelah mereka scan QR code.</p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                            @foreach($ongoingEntries as $e)
                                @php
                                    $avatarChar = strtoupper(substr($e->name ?? 'T', 0, 1));
                                    $scans = $e->scans()->orderByDesc('scanned_at')->limit(5)->get();
                                @endphp
                                <div wire:key="ongoing-{{ $e->guestbook_id }}"
                                     class="bg-white border border-blue-200 rounded-xl p-4 flex flex-col gap-3 hover:shadow-md hover:border-blue-300 transition">
                                    {{-- Header row --}}
                                    <div class="flex items-start gap-3">
                                        <div class="{{ $icoAvatar }} bg-blue-600">{{ $avatarChar }}</div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between gap-2">
                                                <p class="font-semibold text-gray-900 truncate">{{ $e->name }}</p>
                                                <span class="shrink-0 inline-flex items-center gap-1 text-[10px] font-semibold px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 border border-blue-200">
                                                    &#128203; Berkunjung
                                                </span>
                                            </div>
                                            @if($e->instansi)
                                                <p class="text-xs text-gray-500 mt-0.5 truncate">{{ $e->instansi }}</p>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Details --}}
                                    <div class="space-y-1 text-xs text-gray-600 bg-gray-50 rounded-lg p-2.5 border border-gray-100">
                                        @if($e->keperluan)
                                            <div class="flex gap-1.5"><span class="text-gray-400 shrink-0">Keperluan:</span><span class="font-medium text-gray-800 truncate">{{ $e->keperluan }}</span></div>
                                        @endif
                                        <div class="flex gap-1.5"><span class="text-gray-400 shrink-0">Masuk:</span><span class="font-semibold text-emerald-700">{{ gbsFmtDate($e->date) }} · {{ gbsFmtTime($e->jam_in) }}</span></div>
                                        <div class="flex gap-1.5">
                                            <span class="text-gray-400 shrink-0">Pengunjung:</span>
                                            <span class="font-bold text-blue-700">{{ $e->visitor_count }} orang</span>
                                        </div>
                                        <div class="flex gap-1.5"><span class="text-gray-400 shrink-0">Petugas:</span><span class="font-medium text-gray-700 truncate">{{ $e->petugas_penjaga }}</span></div>
                                    </div>

                                    {{-- Recent scans list --}}
                                    @if($scans->count())
                                        <div class="rounded-lg border border-blue-100 bg-blue-50/50 px-2.5 py-2 space-y-1">
                                            <p class="text-[10px] font-semibold uppercase tracking-wider text-blue-500 mb-1.5">Pengunjung Hadir</p>
                                            @foreach($scans as $scan)
                                                <div class="flex items-center gap-2 text-xs">
                                                    <div class="w-5 h-5 rounded-full bg-blue-200 flex items-center justify-center text-[9px] font-bold text-blue-700 shrink-0">
                                                        {{ strtoupper(substr($scan->visitor_name ?? 'T', 0, 1)) }}
                                                    </div>
                                                    <span class="font-medium text-gray-800 truncate">{{ $scan->visitor_name ?? 'Tamu' }}</span>
                                                    <span class="ml-auto text-[10px] text-gray-400 shrink-0">{{ \Carbon\Carbon::parse($scan->scanned_at)->format('H:i') }}</span>
                                                </div>
                                            @endforeach
                                            @if($e->visitor_count > 5)
                                                <p class="text-[10px] text-blue-400 text-center pt-0.5">+{{ $e->visitor_count - 5 }} lainnya</p>
                                            @endif
                                        </div>
                                    @endif

                                    {{-- Actions --}}
                                    <div class="pt-2 border-t border-gray-100 flex items-center justify-end gap-1.5">
                                        <button wire:click="openEdit({{ $e->guestbook_id }})"
                                                class="px-2.5 py-1.5 text-xs font-semibold rounded-lg text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 transition focus:outline-none">
                                            Edit
                                        </button>
                                        <button wire:click="checkOutNow({{ $e->guestbook_id }})"
                                                wire:confirm="Checkout semua {{ $e->visitor_count }} pengunjung dari rombongan ini?"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-[#4E653D] text-white hover:bg-[#354C2B] transition shadow-sm focus:outline-none">
                                            <x-heroicon-o-arrow-right-start-on-rectangle class="w-3.5 h-3.5"/>
                                            Checkout
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-5">{{ $ongoingEntries->links() }}</div>
                    @endif
                @endif
            </div>
        </div>

    </main>

    {{-- ===== EDIT MODAL ===== --}}
    @if($showEdit)
        <div class="fixed inset-0 z-50 flex items-center justify-center px-4" x-data>
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="$set('showEdit', false)"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
                <div class="bg-[#4A2F24] px-6 py-4 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-[#CDDEA7]">Edit Data Tamu</h3>
                    <button wire:click="$set('showEdit', false)" class="text-[#CDDEA7]/60 hover:text-[#CDDEA7]">
                        <x-heroicon-o-x-mark class="w-5 h-5"/>
                    </button>
                </div>
                <div class="p-6 space-y-4">
                    @php
                        $mi = 'w-full h-10 px-3.5 rounded-lg border border-gray-300 bg-white text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-900/10 focus:border-gray-900 transition-all';
                        $ml = 'block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1';
                    @endphp
                    <div>
                        <label class="{{ $ml }}">Nama Lengkap <span class="text-rose-500">*</span></label>
                        <input type="text" wire:model.defer="edit.name" class="{{ $mi }}">
                        @error('edit.name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="{{ $ml }}">Email</label>
                        <input type="email" wire:model.defer="edit.email" class="{{ $mi }}">
                        @error('edit.email') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="{{ $ml }}">No. Telepon</label>
                        <input type="text" wire:model.defer="edit.phone_number" class="{{ $mi }}">
                    </div>
                    <div>
                        <label class="{{ $ml }}">Instansi</label>
                        <input type="text" wire:model.defer="edit.instansi" class="{{ $mi }}">
                    </div>
                    <div>
                        <label class="{{ $ml }}">Keperluan</label>
                        <input type="text" wire:model.defer="edit.keperluan" class="{{ $mi }}">
                    </div>
                    <div>
                        <label class="{{ $ml }}">Petugas <span class="text-rose-500">*</span></label>
                        <input type="text" wire:model.defer="edit.petugas_penjaga" class="{{ $mi }}">
                        @error('edit.petugas_penjaga') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="px-6 pb-6 flex justify-end gap-2">
                    <button wire:click="$set('showEdit', false)"
                            class="px-4 py-2 text-xs font-semibold rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition">
                        Batal
                    </button>
                    <button wire:click="saveEdit"
                            wire:loading.attr="disabled"
                            class="px-5 py-2 text-xs font-semibold rounded-lg bg-[#4E653D] text-white hover:bg-[#354C2B] transition shadow-sm">
                        Simpan
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
