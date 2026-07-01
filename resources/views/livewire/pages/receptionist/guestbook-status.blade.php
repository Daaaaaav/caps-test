<div class="min-h-screen bg-gray-50" wire:poll.30s>
    @php
        use Carbon\Carbon;
        $card      = 'bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden';
        $label     = 'block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1.5';
        $input     = 'w-full h-10 px-3.5 rounded-lg border border-gray-300 bg-white text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-900/10 focus:border-gray-900 transition-all';
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
                        <div class="w-12 h-12 bg-[#CDDEA7]/10 rounded-xl flex items-center justify-center backdrop-blur-sm border border-[#CDDEA7]/20">
                            <x-heroicon-o-qr-code class="w-6 h-6 text-[#CDDEA7]"/>
                        </div>
                        <div>
                            <h2 class="text-lg sm:text-xl font-semibold">{{ __('app.guestbook_status_title') }}</h2>
                            <p class="text-xs text-[#CDDEA7]/75 mt-0.5">{{ __('app.guestbook_status_subtitle') }}</p>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- Summary stat pills --}}
        <div class="flex flex-wrap gap-3">
            <div class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#4E653D]/10 border border-[#4E653D]/25 rounded-xl text-sm font-semibold text-[#4E653D] shadow-sm">
                <span class="w-2.5 h-2.5 rounded-full bg-[#4E653D] animate-pulse inline-block"></span>
                {{ $activeCount }} {{ __('app.total_active') }}
            </div>
        </div>

        {{-- Main card --}}
        <div class="{{ $card }}">

            {{-- Search & Filters --}}
            <div class="px-4 sm:px-6 pt-4 pb-3 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-end gap-3">
                <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                    @if($petugasFilter)
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-[#4A2F24] text-[#CDDEA7] text-xs font-semibold">
                            <x-heroicon-o-user class="w-3 h-3"/>
                            {{ $petugasFilter }}
                            <button type="button" wire:click="clearPetugasFilter" class="ml-0.5 hover:text-white font-bold">×</button>
                        </span>
                    @endif
                    <div class="relative w-full sm:w-auto">
                        <input type="text" wire:model.live.debounce.300ms="q"
                            placeholder="{{ __('app.search') }}…"
                            class="h-9 pl-8 pr-3 rounded-lg border border-gray-300 bg-white text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-900/10 focus:border-gray-900 transition-all w-full sm:w-64">
                        <x-heroicon-o-magnifying-glass class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400"/>
                    </div>
                </div>
            </div>

            {{-- Card grid --}}
            <div class="p-4 sm:p-6">

                {{-- ===== ACTIVE GUESTS ===== --}}
                @if($activeEntries->isEmpty())
                    <div class="py-16 text-center text-gray-500 text-sm">
                        <div class="w-14 h-14 mx-auto mb-3 rounded-full bg-[#4E653D]/10 border border-[#4E653D]/20 flex items-center justify-center">
                            <x-heroicon-o-user-group class="w-7 h-7 text-[#4E653D]"/>
                        </div>
                        <p class="font-semibold text-gray-700">Tidak ada tamu yang aktif</p>
                        <p class="text-xs text-gray-400 mt-1">Belum ada kunjungan yang berlangsung saat ini</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                        @foreach($activeEntries as $e)
                            @php
                                $avatarChar = strtoupper(substr($e->name ?? 'G', 0, 1));
                                $scans = $e->scans()->orderByDesc('scanned_at')->limit(5)->get();
                            @endphp
                            <div wire:key="active-{{ $e->guestbook_id }}"
                                 class="bg-white border border-[#4E653D]/25 rounded-xl p-4 flex flex-col gap-3 hover:shadow-md hover:border-[#4E653D]/40 transition">
                                {{-- Header --}}
                                <div class="flex items-start gap-3">
                                    <div class="{{ $icoAvatar }} bg-[#4E653D]">{{ $avatarChar }}</div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between gap-2">
                                            <p class="font-semibold text-gray-900 truncate">{{ $e->name }}</p>
                                            @if($e->storage_place)
                                                <div class="w-8 h-8 rounded-full bg-[#4E653D] text-white flex items-center justify-center text-sm font-bold shrink-0 shadow-sm" title="Tempat Penyimpanan">
                                                    {{ $e->storage_place }}
                                                </div>
                                            @endif
                                        </div>
                                        @if($e->instansi)
                                            <p class="text-xs text-gray-500 mt-0.5 truncate">{{ $e->instansi }}</p>
                                        @endif
                                    </div>
                                </div>

                                {{-- Details --}}
                                <div class="space-y-1 text-xs text-gray-600 bg-gray-50 rounded-lg p-2.5 border border-gray-100">
                                    @if($e->keperluan)
                                        <div class="flex gap-1.5">
                                            <span class="text-gray-400 shrink-0">{{ __('app.visit_purpose_label') }}:</span>
                                            <span class="font-medium text-gray-800 truncate">{{ $e->keperluan }}</span>
                                        </div>
                                    @endif
                                    <div class="flex gap-1.5">
                                        <span class="text-gray-400 shrink-0">{{ __('app.check_in_label') }}:</span>
                                        <span class="font-semibold text-emerald-700">{{ gbsFmtDate($e->date) }} · {{ gbsFmtTime($e->jam_in) }}</span>
                                    </div>
                                    @if($e->email && !$e->qr_status)
                                        <div class="flex gap-1.5">
                                            <span class="text-gray-400 shrink-0">{{ __('app.email') }}:</span>
                                            <span class="font-medium text-gray-700 truncate">{{ $e->email }}</span>
                                        </div>
                                    @endif
                                    <div class="flex gap-1.5">
                                        <span class="text-gray-400 shrink-0">{{ __('app.officer_label') }}:</span>
                                        <span class="font-medium text-gray-700 truncate">{{ $e->petugas_penjaga }}</span>
                                    </div>
                                </div>

                                {{-- QR Info / Recent Scans --}}
                                @if($e->qr_status === 'ongoing' && $scans->count())
                                    <div class="rounded-lg border border-[#4A2F24]/15 bg-[#4A2F24]/5 px-2.5 py-2 space-y-1">
                                        <p class="text-[10px] font-semibold uppercase tracking-wider text-[#4A2F24]/70 mb-1.5">{{ __('app.visitors_present') }}</p>
                                        @foreach($scans as $scan)
                                            <div class="flex items-center gap-2 text-xs">
                                                <div class="w-5 h-5 rounded-full bg-[#4A2F24]/15 flex items-center justify-center text-[9px] font-bold text-[#4A2F24] shrink-0">
                                                    {{ strtoupper(substr($scan->visitor_name ?? 'G', 0, 1)) }}
                                                </div>
                                                <span class="font-medium text-gray-800 truncate">{{ $scan->visitor_name ?? __('app.no_data') }}</span>
                                                <span class="ml-auto text-[10px] text-gray-400 shrink-0">{{ \Carbon\Carbon::parse($scan->scanned_at)->format('H:i') }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @elseif($e->qr_token && $e->qr_status !== 'ongoing')
                                    <div class="flex items-center justify-between text-[11px] bg-[#4E653D]/5 rounded-lg px-2.5 py-1.5 border border-[#4E653D]/15">
                                        <div class="flex items-center gap-2 text-[#4E653D]">
                                            <x-heroicon-o-qr-code class="w-3.5 h-3.5 shrink-0"/>
                                            <span class="truncate">{{ __('app.qr_sent_not_scanned') }}</span>
                                        </div>
                                        @if($e->email)
                                            <button wire:click="resendQr({{ $e->guestbook_id }})"
                                                    wire:loading.attr="disabled"
                                                    wire:target="resendQr({{ $e->guestbook_id }})"
                                                    class="shrink-0 text-[#4A2F24] hover:underline font-semibold ml-2 focus:outline-none flex items-center gap-1">
                                                <span wire:loading.remove wire:target="resendQr({{ $e->guestbook_id }})">
                                                    {{ __('app.resend_qr') }}
                                                </span>
                                                <span wire:loading wire:target="resendQr({{ $e->guestbook_id }})" class="flex items-center gap-0.5">
                                                    <x-heroicon-o-arrow-path class="animate-spin w-3.5 h-3.5" />
                                                    Resending...
                                                </span>
                                            </button>
                                        @endif
                                    </div>
                                @endif

                                {{-- Actions --}}
                                <div class="pt-2 border-t border-gray-100 flex items-center justify-end gap-1.5 mt-auto">
                                    <button wire:click="openEdit({{ $e->guestbook_id }})"
                                            class="px-2.5 py-1.5 text-xs font-semibold rounded-lg text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 transition focus:outline-none">
                                        {{ __('app.edit') }}
                                    </button>
                                    @if($e->qrCodes()->count() > 0)
                                        <a href="{{ route('receptionist.guestbook.checkout', $e->guestbook_id) }}"
                                           class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-[#4A2F24] text-[#CDDEA7] hover:bg-[#3a2319] transition shadow-sm focus:outline-none">
                                            <x-heroicon-o-qr-code class="w-3.5 h-3.5"/>
                                            Scan Checkout
                                            @if($e->qr_status === 'ongoing')
                                                ({{ $e->scannedQrCount() }}/{{ $e->visitor_count }})
                                            @endif
                                        </a>
                                    @endif
                                    <button wire:click="checkOutNow({{ $e->guestbook_id }})"
                                            wire:confirm="{{ __('app.checkout_confirm') }}"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-[#4E653D] text-white hover:bg-[#354C2B] transition shadow-sm focus:outline-none">
                                        <x-heroicon-o-arrow-right-start-on-rectangle class="w-3.5 h-3.5"/>
                                        {{ __('app.checkout_btn') }}
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-5">{{ $activeEntries->links() }}</div>
                @endif

            </div>
        </div>

    </main>

    {{-- ===== EDIT MODAL ===== --}}
    @if($showEdit)
        <div class="fixed inset-0 z-50 flex items-center justify-center px-4">
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="$set('showEdit', false)"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
                <div class="bg-[#4A2F24] px-6 py-4 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-[#CDDEA7]">{{ __('app.edit_guest_title') }}</h3>
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
                        <label class="{{ $ml }}">{{ __('app.full_name_required') }} <span class="text-rose-500">*</span></label>
                        <input type="text" wire:model.defer="edit.name" class="{{ $mi }}">
                        @error('edit.name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="{{ $ml }}">{{ __('app.email') }}</label>
                        <input type="email" wire:model.defer="edit.email" class="{{ $mi }}">
                        @error('edit.email') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="{{ $ml }}">{{ __('app.phone') }}</label>
                        <input type="text" wire:model.defer="edit.phone_number" class="{{ $mi }}">
                    </div>
                    <div>
                        <label class="{{ $ml }}">{{ __('app.institution') }}</label>
                        <input type="text" wire:model.defer="edit.instansi" class="{{ $mi }}">
                    </div>
                    <div>
                        <label class="{{ $ml }}">{{ __('app.visit_purpose') }}</label>
                        <input type="text" wire:model.defer="edit.keperluan" class="{{ $mi }}">
                    </div>
                    <div>
                        <label class="{{ $ml }}">{{ __('app.officer_label') }} <span class="text-rose-500">*</span></label>
                        <input type="text" wire:model.defer="edit.petugas_penjaga" class="{{ $mi }}">
                        @error('edit.petugas_penjaga') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="px-6 pb-6 flex justify-end gap-2">
                    <button wire:click="$set('showEdit', false)"
                            class="px-4 py-2 text-xs font-semibold rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition">
                        {{ __('app.cancel') }}
                    </button>
                    <button wire:click="saveEdit"
                            wire:loading.attr="disabled"
                            class="px-5 py-2 text-xs font-semibold rounded-lg bg-[#4E653D] text-white hover:bg-[#354C2B] transition shadow-sm">
                        {{ __('app.save') }}
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
