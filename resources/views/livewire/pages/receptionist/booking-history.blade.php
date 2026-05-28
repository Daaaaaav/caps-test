<div class="min-h-screen bg-gray-50" wire:poll.1000ms.keep-alive>
    @php
    use Carbon\Carbon;

    if (!function_exists('fmtDate')) {
        function fmtDate($v) {
            try { return $v ? Carbon::parse($v)->format('d M Y') : '—'; }
            catch (\Throwable) { return '—'; }
        }
    }
    if (!function_exists('fmtTime')) {
        function fmtTime($v) {
            try { return $v ? Carbon::parse($v)->format('H.i') : '—'; } // 10.00
            catch (\Throwable) {
                if (is_string($v)) {
                    if (preg_match('/^\d{2}:\d{2}/', $v)) return str_replace(':','.', substr($v,0,5));
                    if (preg_match('/^\d{2}\.\d{2}/', $v)) return substr($v,0,5);
                }
                return '—';
            }
        }
    }

    /** @var int|null $roomFilterId */
    $roomFilterId = $roomFilterId ?? null;

    $card      = 'bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden';
    $label     = 'block text-sm font-medium text-gray-700 mb-2';
    $input     = 'w-full h-10 px-3 rounded-lg border border-gray-300 text-gray-800 placeholder:text-gray-400 focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10 bg-white transition';
    $btnBlk    = 'px-3 py-2 text-xs font-medium rounded-lg bg-[#4E653D] text-white hover:bg-[#354C2B] focus:outline-none focus:ring-2 focus:ring-[#4E653D]/20 disabled:opacity-60 transition shadow-sm';
    $chip      = 'inline-flex items-center gap-2 px-2.5 py-1 rounded-lg bg-gray-100 text-xs';
    $icoAvatar = 'w-10 h-10 bg-[#4E653D] rounded-xl flex items-center justify-center text-white font-semibold text-sm shrink-0';
    @endphp

    <style>
        :root { color-scheme: light; }
        select, option { color:#111827 !important; background:#ffffff !important; -webkit-text-fill-color:#111827 !important; }
        option:checked { background:#e5e7eb !important; color:#111827 !important; }
    </style>

    <main class="px-4 sm:px-6 py-6 space-y-6">
        {{-- HERO --}}
        <div class="relative overflow-hidden rounded-2xl bg-[#4A2F24] text-[#CDDEA7] shadow-2xl">
            <div class="pointer-events-none absolute inset-0 opacity-10">
                <div class="absolute top-0 -right-4 w-24 h-24 bg-[#CDDEA7] rounded-full blur-xl"></div>
                <div class="absolute bottom-0 -left-4 w-16 h-16 bg-[#CDDEA7] rounded-full blur-lg"></div>
            </div>
            <div class="relative z-10 p-6 sm:p-8">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-[#CDDEA7]/10 rounded-xl flex items-center justify-center backdrop-blur-sm border border-[#CDDEA7]/20">
                            <x-heroicon-o-clock class="w-6 h-6 text-[#CDDEA7]"/>
                        </div>
                        <div>
                            <h2 class="text-lg sm:text-xl font-semibold">{{ __('app.booking_history_title') }}</h2>
                            <p class="text-sm text-[#CDDEA7]/80">
                                Lihat dan kelola riwayat booking yang sudah selesai atau ditolak.
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        {{-- Show deleted toggle --}}
                        <label class="inline-flex items-center gap-2 text-sm text-[#CDDEA7]/90">
                            <input type="checkbox"
                                   wire:model.live="withTrashed"
                                   class="rounded border-[#CDDEA7]/30 bg-[#CDDEA7]/10 focus:ring-[#CDDEA7]/40 text-[#CDDEA7]">
                            <span>{{ __('app.show_deleted') }}</span>
                        </label>
 
                        {{-- MOBILE FILTER BUTTON --}}
                        <button type="button"
                                class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-[#CDDEA7]/10 text-xs font-medium border border-[#CDDEA7]/30 hover:bg-[#CDDEA7]/20 md:hidden"
                                wire:click="openFilterModal">
                            <x-heroicon-o-funnel class="w-4 h-4"/>
                            <span>{{ __('app.filter') }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- MAIN LAYOUT --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            {{-- LEFT: HISTORY LIST CARD --}}
            <section class="{{ $card }} md:col-span-3">
                {{-- Header: title + tabs + room badge + type scope --}}
                <div class="px-4 sm:px-6 pt-4 pb-3 border-b border-gray-200 space-y-3">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900">{{ __('app.history') }}</h3>
                            <p class="text-xs text-gray-500">
                                {{ __('app.history_subtitle') }}
                            </p>
                        </div>

                        {{-- Tabs + View Mode Toggle --}}
                        <div class="flex items-center gap-3 self-start sm:self-auto">
                            {{-- Tabs --}}
                            <div class="inline-flex items-center bg-gray-100 rounded-full p-1 text-xs font-medium">
                                <button type="button"
                                        wire:click="setTab('done')"
                                        class="px-3 py-1 rounded-full transition
                                            {{ $activeTab === 'done'
                                                ? 'bg-[#4E653D] text-white shadow-sm'
                                                : 'text-gray-700 hover:bg-gray-200' }}">
                                    Done
                                </button>
                                <button type="button"
                                        wire:click="setTab('rejected')"
                                        class="px-3 py-1 rounded-full transition
                                            {{ $activeTab === 'rejected'
                                                ? 'bg-[#4E653D] text-white shadow-sm'
                                                : 'text-gray-700 hover:bg-gray-200' }}">
                                    Rejected
                                </button>
                            </div>

                            {{-- Layout Toggler --}}
                            <div class="flex items-center gap-1 bg-gray-100 p-1 rounded-lg shrink-0 border border-gray-200/50">
                                <button type="button" 
                                        wire:click="setViewMode('card')" 
                                        class="p-1.5 rounded-md transition-all {{ $viewMode === 'card' ? 'bg-white text-gray-800 shadow-sm border border-gray-200/40' : 'text-gray-400 hover:text-gray-600' }}"
                                        title="Card View">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                    </svg>
                                </button>
                                <button type="button" 
                                        wire:click="setViewMode('table')" 
                                        class="p-1.5 rounded-md transition-all {{ $viewMode === 'table' ? 'bg-white text-gray-800 shadow-sm border border-gray-200/40' : 'text-gray-400 hover:text-gray-600' }}"
                                        title="Table View">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Room badge + type scope --}}
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 text-xs mt-1">
                        <div class="flex flex-wrap items-center gap-2">
                            @if(!is_null($roomFilterId))
                                @php $activeRoom = collect($roomsOptions)->firstWhere('id', $roomFilterId); @endphp
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-[#4A2F24] text-[#CDDEA7] border border-[#4A2F24]/30">
                                    <x-heroicon-o-building-office class="w-3.5 h-3.5"/>
                                    <span>Room: {{ $activeRoom['label'] ?? 'Unknown' }}</span>
                                    <button type="button" class="ml-1 hover:text-white" wire:click="clearRoomFilter">×</button>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-gray-100 text-gray-700 border border-dashed border-gray-300">
                                    <x-heroicon-o-funnel class="w-3.5 h-3.5"/>
                                    <span>{{ __('app.no_room_filter') }}</span>
                                </span>
                            @endif
                        </div>

                        <div class="inline-flex items-center bg-gray-100 rounded-full p-1 text-[11px] font-medium">
                            <button type="button"
                                    wire:click="setTypeScope('all')"
                                    class="px-3 py-1 rounded-full transition
                                        {{ $typeScope === 'all'
                                            ? 'bg-[#4E653D] text-white shadow-sm'
                                            : 'text-gray-700 hover:bg-gray-200' }}">
                                All
                            </button>
                            <button type="button"
                                    wire:click="setTypeScope('offline')"
                                    class="px-3 py-1 rounded-full transition
                                        {{ $typeScope === 'offline'
                                            ? 'bg-[#4E653D] text-white shadow-sm'
                                            : 'text-gray-700 hover:bg-gray-200' }}">
                                Offline
                            </button>
                            <button type="button"
                                    wire:click="setTypeScope('online')"
                                    class="px-3 py-1 rounded-full transition
                                        {{ $typeScope === 'online'
                                            ? 'bg-[#4E653D] text-white shadow-sm'
                                            : 'text-gray-700 hover:bg-gray-200' }}">
                                Online
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Filters --}}
                <div class="px-4 sm:px-6 pt-4 pb-3 border-b border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="{{ $label }}">{{ __('app.search') }}</label>
                            <div class="relative">
                                <input type="text"
                                       class="{{ $input }} pl-9"
                                       placeholder="Cari judul…"
                                       wire:model.live="q">
                                <x-heroicon-o-magnifying-glass class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"/>
                            </div>
                        </div>

                        <div>
                            <label class="{{ $label }}">Tanggal</label>
                            <div class="relative">
                                <input type="date"
                                       class="{{ $input }} pl-9"
                                       wire:model.live="selectedDate">
                                <x-heroicon-o-calendar class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"/>
                            </div>
                        </div>

                        <div>
                            <label class="{{ $label }}">Urutkan</label>
                            <select wire:model.live="dateMode" class="{{ $input }}">
                                <option value="semua">Default (terbaru)</option>
                                <option value="terbaru">Tanggal terbaru</option>
                                <option value="terlama">Tanggal terlama</option>
                            </select>
                        </div>
                    </div>
                             {{-- LIST AREA (grid cards, same style as Approval) --}}
                {{-- DONE TAB --}}
                @if($activeTab === 'done')
                    @if($doneRows->isEmpty())
                        <div class="px-4 sm:px-6 py-14 text-center text-gray-500 text-sm">
                            Tidak ada data.
                        </div>
                    @else
                        <div class="px-4 sm:px-6 py-5">
                            @if($viewMode === 'card')
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                    @foreach($doneRows as $row)
                                        @php
                                            $isOnline   = in_array($row->booking_type, ['onlinemeeting','online_meeting']);
                                            $isRoomType = in_array($row->booking_type, ['bookingroom','meeting']);
                                            $stateKey   = $row->deleted_at ? 'trash' : 'ok';
                                            $avatarChar = strtoupper(substr($row->meeting_title ?? '—', 0, 1));

                                            $platform = $row->online_meeting_platform
                                                        ?? $row->platform
                                                        ?? $row->meeting_platform
                                                        ?? $row->online_provider
                                                        ?? ($isOnline ? 'Online Meeting' : null);

                                            $meetingUrl      = $row->online_meeting_url ?? null;
                                            $meetingCode     = $row->online_meeting_code ?? null;
                                            $meetingPassword = $row->online_meeting_password ?? null;

                                            $requesterName = $row->user?->name
                                                            ?? $row->requester_name
                                                            ?? null;

                                            $requesterDept = $row->user?->department?->department_name
                                                            ?? $row->user?->department?->dept_name
                                                            ?? $row->department_name
                                                            ?? null;
                                        @endphp

                                        {{-- START: MODIFIED HISTORY CARD DESIGN (DONE) --}}
                                        <div wire:key="done-{{ $row->bookingroom_id }}-{{ $stateKey }}"
                                             class="bg-white border border-gray-200 rounded-xl p-4 space-y-3 hover:shadow-sm hover:border-gray-300 transition">
                                            
                                            <div class="flex items-start gap-4">
                                                {{-- 1. Avatar/Initial on the left --}}
                                                <div class="{{ $icoAvatar }} mt-0.5">{{ $avatarChar }}</div>
                                                
                                                <div class="flex-1 min-w-0">
                                                    {{-- 2. TOP ROW: Title, Type, Status --}}
                                                    <div class="flex items-center justify-between gap-3 min-w-0 mb-2">
                                                        <h4 class="font-semibold text-gray-900 text-base truncate pr-2">
                                                            {{ $row->meeting_title ?? '—' }}
                                                        </h4>
                                                        <div class="flex-shrink-0 flex items-center gap-2">
                                                            {{-- Type (Offline/Online) --}}
                                                            <span class="text-[11px] px-2 py-0.5 rounded-full border flex-shrink-0 {{ $isOnline ? 'border-emerald-300 text-emerald-700 bg-emerald-50' : 'border-blue-300 text-blue-700 bg-blue-50' }}">
                                                                {{ $isOnline ? 'ONLINE' : 'OFFLINE' }}
                                                            </span>
                                                            {{-- Status (Done) --}}
                                                            <span class="text-[11px] px-2 py-0.5 rounded-full bg-green-100 text-green-800 flex-shrink-0">
                                                                DONE
                                                            </span>
                                                            @if($row->deleted_at)
                                                                <span class="text-[11px] px-2 py-0.5 rounded-full bg-rose-100 text-rose-800 flex-shrink-0">
                                                                    DELETED
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>

                                                    {{-- 3. MIDDLE SECTION: Date, Time, Room --}}
                                                    <div class="space-y-2 text-[13px] text-gray-600 mb-3 border-y border-gray-100 py-2">
                                                        <div class="flex items-center gap-5">
                                                            <span class="flex items-center gap-1.5 font-medium text-gray-800">
                                                                <x-heroicon-o-calendar class="w-4 h-4 text-gray-500"/>
                                                                {{ fmtDate($row->date) }}
                                                            </span>
                                                            <span class="flex items-center gap-1.5 font-medium text-gray-800">
                                                                <x-heroicon-o-clock class="w-4 h-4 text-gray-500"/>
                                                                {{ fmtTime($row->start_time) }}–{{ fmtTime($row->end_time) }}
                                                            </span>
                                                        </div>
                                                        @if($isRoomType)
                                                            {{-- Room/Location Chip --}}
                                                            <span class="{{ $chip }} text-xs px-2.5 py-0.5">
                                                                <x-heroicon-o-building-office class="w-3.5 h-3.5 text-gray-500"/>
                                                                <span class="font-medium text-gray-700">
                                                                    Room: {{ optional($row->room)->room_name ?? '—' }}
                                                                </span>
                                                            </span>
                                                        @elseif($isOnline && $platform)
                                                            {{-- Online Platform Chip --}}
                                                            <span class="{{ $chip }} text-xs px-2.5 py-0.5 bg-emerald-50 border border-emerald-100 text-emerald-700">
                                                                <x-heroicon-o-folder class="w-3.5 h-3.5 text-emerald-500"/>
                                                                <span class="font-medium">{{ $platform }}</span>
                                                            </span>
                                                        @endif
                                                        
                                                        {{-- Online link/code (Combined into one section) --}}
                                                        @if($isOnline && ($meetingUrl || $meetingCode || $meetingPassword))
                                                            <div class="flex flex-wrap items-center gap-2 mt-2 pt-1 text-[11px] border-t border-dashed border-gray-100">
                                                                @if($meetingUrl)
                                                                    <a href="{{ $meetingUrl }}" target="_blank"
                                                                       class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-[#4A2F24] text-[#CDDEA7] hover:bg-[#352018] shadow-sm">
                                                                        <x-heroicon-o-link class="w-3.5 h-3.5"/>
                                                                        Join link
                                                                    </a>
                                                                @endif
                                                                @if($meetingCode)
                                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-gray-100 text-gray-700 border border-gray-200">
                                                                        Code:
                                                                        <span class="font-mono">{{ $meetingCode }}</span>
                                                                    </span>
                                                                @endif
                                                                @if($meetingPassword)
                                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-gray-100 text-gray-700 border border-gray-200">
                                                                        Pass:
                                                                        <span class="font-mono">{{ $meetingPassword }}</span>
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        @endif
                                                    </div>

                                                    {{-- 4. BOTTOM LEFT: Requester Info & Notes --}}
                                                    <div class="text-[12px] text-gray-600 space-y-2">
                                                        @if($requesterName)
                                                            <p>{{ __('app.requested_by') }} <span class="font-medium text-gray-800">{{ $requesterName }}</span></p>
                                                        @endif
                                                        @if($requesterDept)
                                                            <span class="px-2 py-0.5 rounded-full bg-gray-100 text-gray-700 text-[11px] border border-gray-200">
                                                                {{ $requesterDept }}
                                                            </span>
                                                        @endif
                                                        @if($row->notes)
                                                            <div class="mt-2 text-xs text-gray-600 bg-gray-50 border border-gray-100 rounded-lg p-2">
                                                                <span class="font-medium">{{ __('app.notes') }}:</span> {{ $row->notes }}
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            {{-- 5. BOTTOM ACTIONS --}}
                                            <div class="pt-3 border-t border-gray-100 flex justify-end gap-3 items-center">
                                                <span class="text-[11px] text-gray-500 mr-auto">No. {{ $doneRows->firstItem() + $loop->index }}</span>
                                                                          {{-- EDIT BUTTON --}}
                                                <button type="button"
                                                        wire:click="edit({{ $row->bookingroom_id }})"
                                                        wire:loading.attr="disabled"
                                                        class="{{ $btnBlk }} px-4 py-2">
                                                    Edit
                                                </button>

                                                @if(!$row->deleted_at)
                                                    {{-- DELETE BUTTON --}}
                                                    <button type="button"
                                                            wire:click="destroy({{ $row->bookingroom_id }})"
                                                            wire:loading.attr="disabled"
                                                            wire:target="destroy"
                                                            class="px-4 py-2 text-xs font-medium rounded-lg bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100 focus:outline-none focus:ring-2 focus:ring-rose-500/20 disabled:opacity-60 transition">
                                                        Delete
                                                    </button>
                                                @else
                                                    {{-- RESTORE BUTTON --}}
                                                    <button type="button"
                                                            wire:click="restore({{ $row->bookingroom_id }})"
                                                            wire:loading.attr="disabled"
                                                            wire:target="restore"
                                                            class="px-4 py-2 text-xs font-medium rounded-lg bg-[#4E653D] text-white hover:bg-[#354C2B] focus:outline-none focus:ring-2 focus:ring-[#4E653D]/20 disabled:opacity-60 transition shadow-sm">
                                                        Restore
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                        {{-- END: MODIFIED HISTORY CARD DESIGN (DONE) --}}
                                    @endforeach
                                </div>
                            @else
                                {{-- Done Table Layout --}}
                                <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white">
                                    <table class="w-full text-left border-collapse">
                                        <thead>
                                            <tr class="border-b border-gray-200 text-[11px] font-bold uppercase tracking-wider text-gray-500 bg-gray-50/70">
                                                <th class="px-6 py-3.5">#</th>
                                                <th class="px-6 py-3.5">{{ __('app.title_col') }}</th>
                                                <th class="px-6 py-3.5">{{ __('app.room_platform') }}</th>
                                                <th class="px-6 py-3.5">{{ __('app.date') }}</th>
                                                <th class="px-6 py-3.5">{{ __('app.time') }}</th>
                                                <th class="px-6 py-3.5">{{ __('app.requester') }}</th>
                                                <th class="px-6 py-3.5 text-right">{{ __('app.actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100">
                                            @foreach($doneRows as $row)
                                                @php
                                                    $isOnline   = in_array($row->booking_type, ['onlinemeeting','online_meeting']);
                                                    $isRoomType = in_array($row->booking_type, ['bookingroom','meeting']);
                                                    $platform = $row->online_meeting_platform
                                                                ?? $row->platform
                                                                ?? $row->meeting_platform
                                                                ?? $row->online_provider
                                                                ?? ($isOnline ? 'Online Meeting' : null);
                                                    $requesterName = $row->user?->name
                                                                    ?? $row->requester_name
                                                                    ?? null;
                                                    $requesterDept = $row->user?->department?->department_name
                                                                    ?? $row->user?->department?->dept_name
                                                                    ?? $row->department_name
                                                                    ?? null;
                                                @endphp
                                                <tr class="hover:bg-gray-50/50 transition text-sm text-gray-700">
                                                    <td class="px-6 py-4 font-mono text-xs font-semibold text-gray-400">#{{ $row->bookingroom_id }}</td>
                                                    <td class="px-6 py-4">
                                                        <div class="font-semibold text-gray-900">{{ $row->meeting_title ?? '—' }}</div>
                                                        @if($row->deleted_at)
                                                            <span class="inline-flex items-center text-[10px] text-rose-600 bg-rose-50 px-1.5 py-0.5 rounded font-medium mt-1">{{ __('app.deleted') }}</span>
                                                        @endif
                                                    </td>
                                                    <td class="px-6 py-4">
                                                        @if($isOnline)
                                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 text-xs font-semibold uppercase border border-emerald-200">
                                                                {{ $platform ?? 'ONLINE' }}
                                                            </span>
                                                        @else
                                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-blue-50 text-blue-700 text-xs font-semibold uppercase border border-blue-200">
                                                                {{ $row->room?->room_name ?? 'OFFLINE' }}
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td class="px-6 py-4 font-medium">{{ fmtDate($row->date) }}</td>
                                                    <td class="px-6 py-4 font-mono text-xs">{{ fmtTime($row->start_time) }}–{{ fmtTime($row->end_time) }}</td>
                                                    <td class="px-6 py-4">
                                                        @if($requesterName)
                                                            <div class="font-semibold text-gray-800">{{ $requesterName }}</div>
                                                        @endif
                                                        @if($requesterDept)
                                                            <div class="text-xs text-gray-500">{{ $requesterDept }}</div>
                                                        @endif
                                                    </td>
                                                    <td class="px-6 py-4 text-right">
                                                        <div class="flex items-center justify-end gap-2">
                                                            <button type="button"
                                                                    wire:click="edit({{ $row->bookingroom_id }})"
                                                                    wire:loading.attr="disabled"
                                                                    class="px-2.5 py-1.5 text-xs font-medium rounded-lg text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 focus:outline-none transition">
                                                                Edit
                                                            </button>
                                                            @if(!$row->deleted_at)
                                                                <button type="button"
                                                                        wire:click="destroy({{ $row->bookingroom_id }})"
                                                                        wire:confirm="Yakin ingin menghapus data booking ini?"
                                                                        wire:loading.attr="disabled"
                                                                        class="px-2.5 py-1.5 text-xs font-medium rounded-lg bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100 focus:outline-none transition">
                                                                    Delete
                                                                </button>
                                                            @else
                                                                <button type="button"
                                                                        wire:click="restore({{ $row->bookingroom_id }})"
                                                                        wire:loading.attr="disabled"
                                                                        class="px-2.5 py-1.5 text-xs font-medium rounded-lg bg-[#4E653D] text-white hover:bg-[#354C2B] focus:outline-none transition">
                                                                    Restore
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    @endif
                @endif

                {{-- REJECTED TAB --}}
                @if($activeTab === 'rejected')
                    @if($rejectedRows->isEmpty())
                        <div class="px-4 sm:px-6 py-14 text-center text-gray-500 text-sm">
                            Tidak ada data.
                        </div>
                    @else
                        <div class="px-4 sm:px-6 py-5">
                            @if($viewMode === 'card')
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                    @foreach($rejectedRows as $row)
                                        @php
                                            $isOnline   = in_array($row->booking_type, ['onlinemeeting','online_meeting']);
                                            $isRoomType = in_array($row->booking_type, ['bookingroom','meeting']);
                                            $stateKey   = $row->deleted_at ? 'trash' : 'ok';
                                            $avatarChar = strtoupper(substr($row->meeting_title ?? '—', 0, 1));

                                            $platform = $row->online_meeting_platform
                                                        ?? $row->platform
                                                        ?? $row->meeting_platform
                                                        ?? $row->online_provider
                                                        ?? ($isOnline ? 'Online Meeting' : null);

                                            $meetingUrl      = $row->online_meeting_url ?? null;
                                            $meetingCode     = $row->online_meeting_code ?? null;
                                            $meetingPassword = $row->online_meeting_password ?? null;

                                            $requesterName = $row->user?->name
                                                            ?? $row->requester_name
                                                            ?? null;

                                            $requesterDept = $row->user?->department?->department_name
                                                            ?? $row->user?->department?->dept_name
                                                            ?? $row->department_name
                                                            ?? null;
                                        @endphp

                                        {{-- START: MODIFIED HISTORY CARD DESIGN (REJECTED) --}}
                                        <div wire:key="rej-{{ $row->bookingroom_id }}-{{ $stateKey }}"
                                             class="bg-white border border-gray-200 rounded-xl p-4 space-y-3 hover:shadow-sm hover:border-gray-300 transition">
                                            
                                            <div class="flex items-start gap-4">
                                                {{-- 1. Avatar/Initial on the left --}}
                                                <div class="{{ $icoAvatar }} mt-0.5">{{ $avatarChar }}</div>
                                                
                                                <div class="flex-1 min-w-0">
                                                    {{-- 2. TOP ROW: Title, Type, Status --}}
                                                    <div class="flex items-center justify-between gap-3 min-w-0 mb-2">
                                                        <h4 class="font-semibold text-gray-900 text-base truncate pr-2">
                                                            {{ $row->meeting_title ?? '—' }}
                                                        </h4>
                                                        <div class="flex-shrink-0 flex items-center gap-2">
                                                            {{-- Type (Offline/Online) --}}
                                                            <span class="text-[11px] px-2 py-0.5 rounded-full border flex-shrink-0 {{ $isOnline ? 'border-emerald-300 text-emerald-700 bg-emerald-50' : 'border-blue-300 text-blue-700 bg-blue-50' }}">
                                                                {{ $isOnline ? 'ONLINE' : 'OFFLINE' }}
                                                            </span>
                                                            {{-- Status (Rejected) --}}
                                                            <span class="text-[11px] px-2 py-0.5 rounded-full bg-red-100 text-red-800 flex-shrink-0">
                                                                REJECTED
                                                            </span>
                                                            @if($row->deleted_at)
                                                                <span class="text-[11px] px-2 py-0.5 rounded-full bg-rose-100 text-rose-800 flex-shrink-0">
                                                                    DELETED
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>

                                                    {{-- 3. MIDDLE SECTION: Date, Time, Room --}}
                                                    <div class="space-y-2 text-[13px] text-gray-600 mb-3 border-y border-gray-100 py-2">
                                                        <div class="flex items-center gap-5">
                                                            <span class="flex items-center gap-1.5 font-medium text-gray-800">
                                                                <x-heroicon-o-calendar class="w-4 h-4 text-gray-500"/>
                                                                {{ fmtDate($row->date) }}
                                                            </span>
                                                            <span class="flex items-center gap-1.5 font-medium text-gray-800">
                                                                <x-heroicon-o-clock class="w-4 h-4 text-gray-500"/>
                                                                {{ fmtTime($row->start_time) }}–{{ fmtTime($row->end_time) }}
                                                            </span>
                                                        </div>
                                                        @if($isRoomType)
                                                            {{-- Room/Location Chip --}}
                                                            <span class="{{ $chip }} text-xs px-2.5 py-0.5">
                                                                <x-heroicon-o-building-office class="w-3.5 h-3.5 text-gray-500"/>
                                                                <span class="font-medium text-gray-700">
                                                                    Room: {{ optional($row->room)->room_name ?? '—' }}
                                                                </span>
                                                            </span>
                                                        @elseif($isOnline && $platform)
                                                            {{-- Online Platform Chip --}}
                                                            <span class="{{ $chip }} text-xs px-2.5 py-0.5 bg-emerald-50 border border-emerald-100 text-emerald-700">
                                                                <x-heroicon-o-folder class="w-3.5 h-3.5 text-emerald-500"/>
                                                                <span class="font-medium">{{ $platform }}</span>
                                                            </span>
                                                        @endif
                                                        
                                                        {{-- Online link/code (Combined into one section) --}}
                                                        @if($isOnline && ($meetingUrl || $meetingCode || $meetingPassword))
                                                            <div class="flex flex-wrap items-center gap-2 mt-2 pt-1 text-[11px] border-t border-dashed border-gray-100">
                                                                @if($meetingUrl)
                                                                    <a href="{{ $meetingUrl }}" target="_blank"
                                                                       class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-[#4A2F24] text-[#CDDEA7] hover:bg-[#352018] shadow-sm">
                                                                        <x-heroicon-o-link class="w-3.5 h-3.5"/>
                                                                        Join link
                                                                    </a>
                                                                @endif
                                                                @if($meetingCode)
                                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-gray-100 text-gray-700 border border-gray-200">
                                                                        Code:
                                                                        <span class="font-mono">{{ $meetingCode }}</span>
                                                                    </span>
                                                                @endif
                                                                @if($meetingPassword)
                                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-gray-100 text-gray-700 border border-gray-200">
                                                                        Pass:
                                                                        <span class="font-mono">{{ $meetingPassword }}</span>
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        @endif
                                                    </div>

                                                    {{-- 4. BOTTOM LEFT: Requester Info & Reject Reason --}}
                                                    <div class="text-[12px] text-gray-600 space-y-2">
                                                        @if($requesterName)
                                                            <p>{{ __('app.requested_by') }} <span class="font-medium text-gray-800">{{ $requesterName }}</span></p>
                                                        @endif
                                                        @if($requesterDept)
                                                            <span class="px-2 py-0.5 rounded-full bg-gray-100 text-gray-700 text-[11px] border border-gray-200">
                                                                {{ $requesterDept }}
                                                            </span>
                                                        @endif
                                                        @if($row->book_reject)
                                                            <div class="mt-2 text-xs text-rose-700 bg-rose-50 border border-rose-100 rounded-lg p-2">
                                                                <span class="font-medium">Alasan penolakan:</span> {{ $row->book_reject }}
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            {{-- 5. BOTTOM ACTIONS --}}
                                            <div class="pt-3 border-t border-gray-100 flex justify-end gap-3 items-center">
                                                <span class="text-[11px] text-gray-500 mr-auto">No. {{ $rejectedRows->firstItem() + $loop->index }}</span>
                                                                          {{-- EDIT BUTTON --}}
                                                <button type="button"
                                                        wire:click="edit({{ $row->bookingroom_id }})"
                                                        wire:loading.attr="disabled"
                                                        class="{{ $btnBlk }} px-4 py-2">
                                                    Edit
                                                </button>

                                                @if(!$row->deleted_at)
                                                    {{-- DELETE BUTTON --}}
                                                    <button type="button"
                                                            wire:click="destroy({{ $row->bookingroom_id }})"
                                                            wire:loading.attr="disabled"
                                                            wire:target="destroy"
                                                            class="px-4 py-2 text-xs font-medium rounded-lg bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100 focus:outline-none focus:ring-2 focus:ring-rose-500/20 disabled:opacity-60 transition">
                                                        Delete
                                                    </button>
                                                @else
                                                    {{-- RESTORE BUTTON --}}
                                                    <button type="button"
                                                            wire:click="restore({{ $row->bookingroom_id }})"
                                                            wire:loading.attr="disabled"
                                                            wire:target="restore"
                                                            class="px-4 py-2 text-xs font-medium rounded-lg bg-[#4E653D] text-white hover:bg-[#354C2B] focus:outline-none focus:ring-2 focus:ring-[#4E653D]/20 disabled:opacity-60 transition shadow-sm">
                                                        Restore
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                        {{-- END: MODIFIED HISTORY CARD DESIGN (REJECTED) --}}
                                    @endforeach
                                </div>
                            @else
                                {{-- Rejected Table Layout --}}
                                <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white">
                                    <table class="w-full text-left border-collapse">
                                        <thead>
                                            <tr class="border-b border-gray-200 text-[11px] font-bold uppercase tracking-wider text-gray-500 bg-gray-50/70">
                                                <th class="px-6 py-3.5">#</th>
                                                <th class="px-6 py-3.5">{{ __('app.title_col') }}</th>
                                                <th class="px-6 py-3.5">{{ __('app.room_platform') }}</th>
                                                <th class="px-6 py-3.5">{{ __('app.date') }}</th>
                                                <th class="px-6 py-3.5">{{ __('app.time') }}</th>
                                                <th class="px-6 py-3.5">{{ __('app.reason') }}</th>
                                                <th class="px-6 py-3.5 text-right">{{ __('app.actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100">
                                            @foreach($rejectedRows as $row)
                                                @php
                                                    $isOnline   = in_array($row->booking_type, ['onlinemeeting','online_meeting']);
                                                    $isRoomType = in_array($row->booking_type, ['bookingroom','meeting']);
                                                    $platform = $row->online_meeting_platform
                                                                ?? $row->platform
                                                                ?? $row->meeting_platform
                                                                ?? $row->online_provider
                                                                ?? ($isOnline ? 'Online Meeting' : null);
                                                @endphp
                                                <tr class="hover:bg-gray-50/50 transition text-sm text-gray-700">
                                                    <td class="px-6 py-4 font-mono text-xs font-semibold text-gray-400">#{{ $row->bookingroom_id }}</td>
                                                    <td class="px-6 py-4">
                                                        <div class="font-semibold text-gray-900">{{ $row->meeting_title ?? '—' }}</div>
                                                        @if($row->deleted_at)
                                                            <span class="inline-flex items-center text-[10px] text-rose-600 bg-rose-50 px-1.5 py-0.5 rounded font-medium mt-1">{{ __('app.deleted') }}</span>
                                                        @endif
                                                    </td>
                                                    <td class="px-6 py-4">
                                                        @if($isOnline)
                                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 text-xs font-semibold uppercase border border-emerald-200">
                                                                {{ $platform ?? 'ONLINE' }}
                                                            </span>
                                                        @else
                                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-blue-50 text-blue-700 text-xs font-semibold uppercase border border-blue-200">
                                                                {{ $row->room?->room_name ?? 'OFFLINE' }}
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td class="px-6 py-4 font-medium">{{ fmtDate($row->date) }}</td>
                                                    <td class="px-6 py-4 font-mono text-xs">{{ fmtTime($row->start_time) }}–{{ fmtTime($row->end_time) }}</td>
                                                    <td class="px-6 py-4">
                                                        <div class="text-xs text-rose-600 font-medium italic truncate max-w-xs" title="{{ $row->book_reject }}">
                                                            {{ $row->book_reject ?? 'No reason provided' }}
                                                        </div>
                                                    </td>
                                                    <td class="px-6 py-4 text-right">
                                                        <div class="flex items-center justify-end gap-2">
                                                            <button type="button"
                                                                    wire:click="edit({{ $row->bookingroom_id }})"
                                                                    wire:loading.attr="disabled"
                                                                    class="px-2.5 py-1.5 text-xs font-medium rounded-lg text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 focus:outline-none transition">
                                                                Edit
                                                            </button>
                                                            @if(!$row->deleted_at)
                                                                <button type="button"
                                                                        wire:click="destroy({{ $row->bookingroom_id }})"
                                                                        wire:confirm="Yakin ingin menghapus data booking ini?"
                                                                        wire:loading.attr="disabled"
                                                                        class="px-2.5 py-1.5 text-xs font-medium rounded-lg bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100 focus:outline-none transition">
                                                                    Delete
                                                                </button>
                                                            @else
                                                                <button type="button"
                                                                        wire:click="restore({{ $row->bookingroom_id }})"
                                                                        wire:loading.attr="disabled"
                                                                        class="px-2.5 py-1.5 text-xs font-medium rounded-lg bg-[#4E653D] text-white hover:bg-[#354C2B] focus:outline-none transition">
                                                                    Restore
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    @endif
                @endif

                {{-- PAGINATION --}}
                <div class="px-4 sm:px-6 py-5 bg-gray-50 border-t border-gray-200">
                    <div class="flex justify-center">
                        @if($activeTab === 'done')
                            {{ $doneRows->onEachSide(1)->links() }}
                        @else
                            {{ $rejectedRows->onEachSide(1)->links() }}
                        @endif
                    </div>
                </div>
            </section>

            {{-- RIGHT: SIDEBAR (ROOM FILTER) --}}
            <aside class="hidden md:flex md:flex-col md:col-span-1 gap-4">
                <section class="{{ $card }}">
                    <div class="px-4 py-4 border-b border-gray-200">
                        <h3 class="text-sm font-semibold text-gray-900">Filter by Room</h3>
                        <p class="text-xs text-gray-500 mt-1">Klik salah satu ruangan untuk mem-filter daftar history.</p>
                    </div>

                    <div class="px-4 py-3 max-h-64 overflow-y-auto">
                        {{-- All rooms --}}
                        <button type="button"
                                wire:click="clearRoomFilter"
                                class="w-full flex items-center justify-between gap-2 px-3 py-2 rounded-lg text-xs font-medium
                                    {{ is_null($roomFilterId) ? 'bg-[#4A2F24] text-[#CDDEA7] shadow-sm' : 'text-gray-800 hover:bg-gray-100' }}">
                            <span class="flex items-center gap-2">
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-md border border-gray-300 text-[11px]">
                                    All
                                </span>
                                <span>All Rooms</span>
                            </span>
                            @if(is_null($roomFilterId))
                                <span class="text-[10px] uppercase tracking-wide opacity-80">Active</span>
                            @endif
                        </button>

                        {{-- Each room --}}
                        <div class="mt-2 space-y-1.5">
                            @forelse($roomsOptions as $r)
                                @php $active = !is_null($roomFilterId) && (int) $roomFilterId === (int) $r['id']; @endphp
                                <button type="button"
                                        wire:click="selectRoom({{ $r['id'] }})"
                                        class="w-full flex items-center justify-between gap-2 px-3 py-2 rounded-lg text-xs
                                            {{ $active ? 'bg-[#4A2F24] text-[#CDDEA7] shadow-sm' : 'text-gray-800 hover:bg-gray-100' }}">
                                    <span class="flex items-center gap-2">
                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-md border border-gray-300 text-[11px]">
                                            {{ substr($r['label'], 0, 2) }}
                                        </span>
                                        <span class="truncate">{{ $r['label'] }}</span>
                                    </span>
                                    @if($active)
                                        <span class="text-[10px] uppercase tracking-wide opacity-80">Active</span>
                                    @endif
                                </button>
                            @empty
                                <p class="text-xs text-gray-500">Tidak ada data ruangan.</p>
                            @endforelse
                        </div>
                    </div>
                </section>
            </aside>
        </div>

        {{-- EDIT / CREATE MODAL --}}
        @if($showModal)
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
                {{-- Backdrop --}}
                <div class="absolute inset-0 bg-black/60 backdrop-blur-md transition-opacity duration-300" wire:click="$set('showModal', false)"></div>

                {{-- Modal Content --}}
                <div class="relative w-full max-w-2xl bg-card rounded-2xl border border-border shadow-2xl overflow-hidden flex flex-col">
                    {{-- Header --}}
                    <div class="px-6 py-5 border-b border-border bg-muted/10 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center">
                                <x-heroicon-o-pencil class="w-4 h-4 text-primary" />
                            </div>
                            <h3 class="font-bold text-foreground text-base tracking-tight">
                                {{ $modalMode === 'create' ? 'Create' : 'Edit' }} History Item
                            </h3>
                        </div>
                        <button type="button" class="w-8 h-8 flex items-center justify-center rounded-lg text-muted-foreground hover:text-foreground hover:bg-muted transition" wire:click="$set('showModal', false)">✕</button>
                    </div>

                    {{-- Body --}}
                    <div class="p-6 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-muted-foreground mb-1.5">{{ __('app.type') }}</label>
                                <select class="w-full h-10 px-3.5 rounded-lg border border-input bg-background text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" wire:model.live="form.booking_type">
                                    <option value="bookingroom">Booking Room</option>
                                    <option value="meeting">Meeting</option>
                                    <option value="onlinemeeting">Online Meeting</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-muted-foreground mb-1.5">Status</label>
                                <select class="w-full h-10 px-3.5 rounded-lg border border-input bg-background text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" wire:model.live="form.status">
                                    <option value="completed">{{ __('app.done') }}</option>
                                    <option value="rejected">{{ __('app.rejected') }}</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-muted-foreground mb-1.5">Meeting Title</label>
                            <input type="text" class="w-full h-10 px-3.5 rounded-lg border border-input bg-background text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" wire:model.live="form.meeting_title">
                            @error('form.meeting_title')
                                <p class="text-xs text-destructive mt-1.5 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-muted-foreground mb-1.5">{{ __('app.date') }}</label>
                                <input type="date" class="w-full h-10 px-3.5 rounded-lg border border-input bg-background text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" wire:model.live="form.date">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-muted-foreground mb-1.5">{{ __('app.start') }}</label>
                                <input type="time" class="w-full h-10 px-3.5 rounded-lg border border-input bg-background text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" wire:model.live="form.start_time">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-muted-foreground mb-1.5">{{ __('app.end') }}</label>
                                <input type="time" class="w-full h-10 px-3.5 rounded-lg border border-input bg-background text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" wire:model.live="form.end_time">
                            </div>
                        </div>

                        @if(in_array($form['booking_type'] ?? null, ['bookingroom','meeting']))
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-muted-foreground mb-1.5">Room</label>
                                <select class="w-full h-10 px-3.5 rounded-lg border border-input bg-background text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" wire:model.live="form.room_id">
                                    <option value="">— Select room —</option>
                                    @foreach(($rooms ?? []) as $r)
                                        <option value="{{ $r['id'] }}">{{ $r['name'] }}</option>
                                    @endforeach
                                </select>
                                @error('form.room_id')
                                    <p class="text-xs text-destructive mt-1.5 font-medium">{{ $message }}</p>
                                @enderror
                            </div>
                        @else
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-muted-foreground mb-1.5">Online Provider</label>
                                <select class="w-full h-10 px-3.5 rounded-lg border border-input bg-background text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" wire:model.live="form.online_provider">
                                    <option value="zoom">Zoom</option>
                                    <option value="google_meet">Google Meet</option>
                                </select>
                                @error('form.online_provider')
                                    <p class="text-xs text-destructive mt-1.5 font-medium">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif

                        @if(($form['status'] ?? null) === 'rejected')
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-muted-foreground mb-1.5">{{ __('app.reject_reason') }} <span class="text-destructive">*</span></label>
                                <textarea
                                    class="w-full px-3.5 py-2.5 rounded-lg border border-input bg-background text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all resize-none"
                                    rows="3"
                                    placeholder="Tuliskan alasan penolakan…"
                                    wire:model.live="form.book_reject"></textarea>
                                @error('form.book_reject')
                                    <p class="text-xs text-destructive mt-1.5 font-medium">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-muted-foreground mb-1.5">{{ __('app.notes') }}</label>
                            <textarea class="w-full px-3.5 py-2.5 rounded-lg border border-input bg-background text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all resize-none"
                                      rows="3"
                                      wire:model.live="form.notes"></textarea>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="border-t border-border px-6 py-4 flex items-center justify-end gap-3 bg-muted/5">
                        <button type="button"
                                wire:click="$set('showModal', false)"
                                wire:loading.attr="disabled"
                                class="h-9 px-4 rounded-lg bg-secondary text-secondary-foreground text-xs font-semibold hover:bg-secondary/80 border border-border transition inline-flex items-center gap-1.5">
                            <x-heroicon-o-arrow-uturn-left class="w-3.5 h-3.5" />
                            <span>{{ __('app.cancel') }}</span>
                        </button>
                        <button type="button"
                                wire:click="save"
                                wire:loading.attr="disabled"
                                class="h-9 px-4 rounded-lg bg-primary text-primary-foreground text-xs font-semibold hover:bg-primary/95 transition shadow-sm inline-flex items-center gap-1.5">
                            <x-heroicon-o-check class="w-3.5 h-3.5" />
                            <span>Simpan</span>
                        </button>
                    </div>
                </div>
            </div>
        @endif

        {{-- MOBILE FILTER MODAL --}}
        @if($showFilterModal)
            <div class="fixed inset-0 z-50 md:hidden flex items-end">
                <div class="absolute inset-0 bg-black/60 backdrop-blur-md transition-opacity duration-300" wire:click="closeFilterModal"></div>
                <div class="relative w-full bg-card rounded-t-2xl shadow-2xl max-h-[85vh] overflow-hidden flex flex-col border-t border-border">
                    <div class="px-5 py-4 border-b border-border flex items-center justify-between bg-muted/10">
                        <div>
                            <h3 class="text-sm font-semibold tracking-tight text-foreground">Filter & Recent</h3>
                            <p class="text-[11px] text-muted-foreground mt-0.5">Filter berdasarkan ruangan & lihat aktivitas terbaru.</p>
                        </div>
                        <button type="button" class="w-8 h-8 flex items-center justify-center rounded-lg text-muted-foreground hover:text-foreground hover:bg-muted transition" wire:click="closeFilterModal">✕</button>
                    </div>

                    <div class="p-5 space-y-4 overflow-y-auto flex-1">
                        <div>
                            <h4 class="text-xs font-bold uppercase tracking-wider text-foreground mb-3 flex items-center gap-1.5">Filter by Room</h4>

                            <button type="button"
                                    wire:click="clearRoomFilter"
                                    class="w-full flex items-center justify-between gap-2 px-3 py-2 rounded-lg text-xs font-medium
                                        {{ is_null($roomFilterId) ? 'bg-[#4A2F24] text-[#CDDEA7] shadow-sm' : 'text-gray-800 hover:bg-gray-100' }}">
                                <span class="flex items-center gap-2">
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-md border border-gray-300 text-[11px]">
                                        All
                                    </span>
                                    <span>All Rooms</span>
                                </span>
                                @if(is_null($roomFilterId))
                                    <span class="text-[10px] uppercase tracking-wide opacity-80">Active</span>
                                @endif
                            </button>

                            <div class="mt-2 space-y-1.5">
                                @forelse($roomsOptions as $r)
                                    @php $active = !is_null($roomFilterId) && (int) $roomFilterId === (int) $r['id']; @endphp
                                    <button type="button"
                                            wire:click="selectRoom({{ $r['id'] }})"
                                            class="w-full flex items-center justify-between gap-2 px-3 py-2 rounded-lg text-xs
                                                {{ $active ? 'bg-[#4A2F24] text-[#CDDEA7] shadow-sm' : 'text-gray-800 hover:bg-gray-100' }}">
                                        <span class="flex items-center gap-2">
                                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-md border border-gray-300 text-[11px]">
                                                {{ substr($r['label'], 0, 2) }}
                                            </span>
                                            <span class="truncate">{{ $r['label'] }}</span>
                                        </span>
                                        @if($active)
                                            <span class="text-[10px] uppercase tracking-wide opacity-80">Active</span>
                                        @endif
                                    </button>
                                @empty
                                    <p class="text-xs text-gray-500">Tidak ada data ruangan.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="p-5 border-t border-border bg-muted/10">
                        <button type="button"
                                class="w-full h-10 rounded-lg bg-primary text-primary-foreground text-xs font-semibold hover:bg-primary/95 transition-colors shadow-sm"
                                wire:click="closeFilterModal">
                            Apply & Close
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </main>
</div>