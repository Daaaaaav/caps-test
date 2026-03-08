<div class="min-h-screen bg-gray-50" wire:poll.1000ms.keep-alive>
    @php
    use Carbon\Carbon;
    use App\Models\Requirement; // ADDED: Required for the temporary bug workaround

    if (!function_exists('fmtDate')) {
        function fmtDate($v) {
            try { return $v ? Carbon::parse($v)->format('d M Y') : '—'; }
            catch (\Throwable) { return '—'; }
        }
    }
    if (!function_exists('fmtTime')) {
        function fmtTime($v) {
            try { return $v ? Carbon::parse($v)->format('H.i') : '—'; }
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

    $card       = 'bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden';
    $label      = 'block text-sm font-medium text-gray-700 mb-2';
    $input      = 'w-full h-10 px-3 rounded-lg border border-gray-300 text-gray-800 placeholder:text-gray-400 focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10 bg-white transition';
    $textareaInput = 'w-full px-3 py-2 rounded-lg border border-gray-300 text-gray-800 placeholder:text-gray-400 focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10 bg-white transition'; // New variable for textarea
    $btnBlk     = 'px-3 py-2 text-xs font-medium rounded-lg bg-gray-900 text-white hover:bg-black focus:outline-none focus:ring-2 focus:ring-gray-900/20 disabled:opacity-60 transition';
    $btnGhost   = 'px-3 py-2 text-xs font-medium rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-300/20 disabled:opacity-60 transition';
    $chip       = 'inline-flex items-center gap-2 px-2.5 py-1 rounded-lg bg-gray-100 text-xs';
    $icoAvatar = 'w-10 h-10 bg-gray-900 rounded-xl flex items-center justify-center text-white font-semibold text-sm shrink-0';
    $detailItem = 'py-3 border-b border-gray-100'; // Added for detail modal
    @endphp

    <style>
        :root { color-scheme: light; }
        select, option { color:#111827 !important; background:#ffffff !important; -webkit-text-fill-color:#111827 !important; }
        option:checked { background:#e5e7eb !important; color:#111827 !important; }
    </style>

    <main class="px-4 sm:px-6 py-6 space-y-6">
        {{-- HERO --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-gray-900 to-black text-white shadow-2xl">
            <div class="pointer-events-none absolute inset-0 opacity-10">
                <div class="absolute top-0 -right-4 w-24 h-24 bg-white rounded-full blur-xl"></div>
                <div class="absolute bottom-0 -left-4 w-16 h-16 bg-white rounded-full blur-lg"></div>
            </div>
            <div class="relative z-10 p-6 sm:px-8">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="space-y-1">
                        <h2 class="text-lg sm:text-xl font-semibold">Bookings Approval (Receptionist)</h2>
                        <p class="text-sm text-white/80">
                            Kelola permintaan booking ruangan (online/offline): approve, reject (wajib isi alasan), atau reschedule.
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        {{-- MOBILE FILTER BUTTON --}}
                        <button type="button"
                            class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-white/10 text-xs font-medium border border-white/30 hover:bg-white/20 md:hidden"
                            wire:click="openFilterModal">
                            <x-heroicon-o-funnel class="w-4 h-4"/>
                            <span>Filter</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- MAIN LAYOUT --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            {{-- LEFT: APPROVAL LIST --}}
            <section class="{{ $card }} md:col-span-3">
                {{-- Header + tabs + room scope --}}
                <div class="px-4 sm:px-6 pt-4 pb-3 border-b border-gray-200 space-y-3">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900">Approval Queue</h3>
                            <p class="text-xs text-gray-500">
                                Kelola booking yang menunggu persetujuan atau sedang berlangsung.
                            </p>
                        </div>

                        <div class="inline-flex items-center bg-gray-100 rounded-full p-1 text-xs font-medium">
                            <button type="button"
                                wire:click="setTab('pending')"
                                class="px-3 py-1 rounded-full {{ $activeTab === 'pending' ? 'bg-gray-900 text-white' : 'text-gray-700 hover:bg-gray-200' }}">
                                Pending
                            </button>
                            <button type="button"
                                wire:click="setTab('ongoing')"
                                class="px-3 py-1 rounded-full {{ $activeTab === 'ongoing' ? 'bg-gray-900 text-white' : 'text-gray-700 hover:bg-gray-200' }}">
                                Ongoing
                            </button>
                        </div>
                    </div>

                    {{-- Room badge + Type scope --}}
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 text-xs mt-1">
                        <div class="flex flex-wrap items-center gap-2">
                            @if(!is_null($roomFilterId))
                                @php $activeRoom = collect($roomsOptions)->firstWhere('id', $roomFilterId); @endphp
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-gray-900 text-white border border-gray-800">
                                    <x-heroicon-o-building-office class="w-3.5 h-3.5"/>
                                    <span>Room: {{ $activeRoom['label'] ?? 'Unknown' }}</span>
                                    <button type="button" class="ml-1 hover:text-gray-200" wire:click="clearRoomFilter">×</button>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-gray-100 text-gray-700 border border-dashed border-gray-300">
                                    <x-heroicon-o-funnel class="w-3.5 h-3.5"/>
                                    <span>No room filter</span>
                                </span>
                            @endif
                        </div>

                        <div class="inline-flex items-center bg-gray-100 rounded-full p-1 text-[11px] font-medium">
                            <button type="button" wire:click="setTypeScope('all')"
                                class="px-3 py-1 rounded-full {{ $typeScope === 'all' ? 'bg-gray-900 text-white' : 'text-gray-700 hover:bg-gray-200' }}">
                                All
                            </button>
                            <button type="button" wire:click="setTypeScope('offline')"
                                class="px-3 py-1 rounded-full {{ $typeScope === 'offline' ? 'bg-gray-900 text-white' : 'text-gray-700 hover:bg-gray-200' }}">
                                Offline
                            </button>
                            <button type="button" wire:click="setTypeScope('online')"
                                class="px-3 py-1 rounded-full {{ $typeScope === 'online' ? 'bg-gray-900 text-white' : 'text-gray-700' }}">
                                Online
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Filter bar: Search + Date + Sort --}}
                <div class="px-4 sm:px-6 pt-4 pb-3 border-b border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="{{ $label }}">Search</label>
                            <div class="relative">
                                <input type="text" class="{{ $input }} pl-9"
                                    placeholder="Cari judul meeting…"
                                    wire:model.debounce.500ms="q">
                                <x-heroicon-o-magnifying-glass class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"/>
                            </div>
                        </div>

                        <div>
                            <label class="{{ $label }}">Tanggal</label>
                            <div class="relative">
                                <input type="date" class="{{ $input }} pl-9" wire:model.live="selectedDate">
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
                </div>

                @php $list = $activeTab === 'pending' ? $pending : $ongoing; @endphp

                {{-- PENDING TAB (MODIFIED FOR IMAGE DESIGN) --}}
                @if($activeTab === 'pending')
                    @if($list->isEmpty())
                        <div class="px-4 sm:px-6 py-14 text-center text-gray-500 text-sm">
                            Tidak ada booking pending dengan filter saat ini.
                        </div>
                    @else
                        <div class="px-4 sm:px-6 py-5">
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                @foreach($list as $b)
                                    @php
                                        $isOnline   = in_array($b->booking_type, ['online_meeting','onlinemeeting']);
                                        $isRoomType = in_array($b->booking_type, ['bookingroom','meeting']);
                                        $avatarChar = strtoupper(substr($b->meeting_title ?? '—', 0, 1));

                                        $platform = $b->online_meeting_platform
                                                    ?? $b->platform
                                                    ?? $b->meeting_platform
                                                    ?? ($isOnline ? 'Online Meeting' : null);

                                        $meetingUrl      = $b->online_meeting_url ?? null;
                                        $meetingCode     = $b->online_meeting_code ?? null;
                                        $meetingPassword = $b->online_meeting_password ?? null;

                                        $requesterName = $b->user?->name
                                                            ?? $b->requester_name
                                                            ?? null;

                                        $requesterDept = $b->user?->department?->department_name
                                                            ?? $b->user?->department?->dept_name
                                                            ?? $b->department_name
                                                            ?? null;
                                    @endphp

                                    {{-- START: MODIFIED CARD DESIGN TO MATCH IMAGE --}}
                                    <div wire:key="pending-{{ $b->bookingroom_id }}"
                                        class="bg-white border border-gray-200 rounded-xl p-4 space-y-3 hover:shadow-sm hover:border-gray-300 transition">
                                        
                                        <div class="flex items-start gap-4">
                                            {{-- 1. Avatar/Initial on the left --}}
                                            <div class="{{ $icoAvatar }} mt-0.5">{{ $b->meeting_title ? $avatarChar : '?' }}</div>
                                            
                                            <div class="flex-1 min-w-0">
                                                {{-- 2. TOP ROW: Title, Type, Status --}}
                                                <div class="flex items-center justify-between gap-3 min-w-0 mb-2">
                                                    <h4 class="font-semibold text-gray-900 text-base truncate pr-2">
                                                        {{ $b->meeting_title ?? 'Untitled meeting' }}
                                                    </h4>
                                                    <div class="flex-shrink-0 flex items-center gap-2">
                                                        {{-- Type (Offline/Online) --}}
                                                        <span class="text-[11px] px-2 py-0.5 rounded-full border flex-shrink-0 {{ $isOnline ? 'border-emerald-300 text-emerald-700 bg-emerald-50' : 'border-blue-300 text-blue-700 bg-blue-50' }}">
                                                            {{ $isOnline ? 'ONLINE' : 'OFFLINE' }}
                                                        </span>
                                                        {{-- Status (Pending) --}}
                                                        <span class="text-[11px] px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 flex-shrink-0">
                                                            {{ strtoupper($b->status) }}
                                                        </span>
                                                    </div>
                                                </div>

                                                {{-- 3. MIDDLE SECTION: Date, Time, Room --}}
                                                <div class="space-y-2 text-[13px] text-gray-600 mb-3 border-y border-gray-100 py-2">
                                                    <div class="flex items-center gap-5">
                                                        <span class="flex items-center gap-1.5 font-medium text-gray-800">
                                                            <x-heroicon-o-calendar class="w-4 h-4 text-gray-500"/>
                                                            {{ fmtDate($b->date) }}
                                                        </span>
                                                        <span class="flex items-center gap-1.5 font-medium text-gray-800">
                                                            <x-heroicon-o-clock class="w-4 h-4 text-gray-500"/>
                                                            {{ fmtTime($b->start_time) }}–{{ fmtTime($b->end_time) }}
                                                        </span>
                                                    </div>
                                                    @if($isRoomType)
                                                        {{-- Room/Location Chip (Matches image structure) --}}
                                                        <span class="{{ $chip }} text-xs px-2.5 py-0.5">
                                                            <x-heroicon-o-building-office class="w-3.5 h-3.5 text-gray-500"/>
                                                            <span class="font-medium {{ $b->room?->room_name ? 'text-gray-700' : 'text-rose-600' }}">
                                                                Room: {{ $b->room?->room_name ?? 'Not selected' }}
                                                            </span>
                                                        </span>
                                                    @elseif($isOnline && $platform)
                                                        {{-- Online Platform Chip --}}
                                                        <span class="{{ $chip }} text-xs px-2.5 py-0.5 bg-emerald-50 border border-emerald-100 text-emerald-700">
                                                            <x-heroicon-o-folder class="w-3.5 h-3.5 text-emerald-500"/>
                                                            <span class="font-medium">{{ $platform }}</span>
                                                        </span>
                                                    @endif
                                                </div>

                                                {{-- 4. BOTTOM LEFT: Requester Info --}}
                                                <div class="text-[12px] text-gray-600">
                                                    @if($requesterName)
                                                        <p class="mb-1">Req. by <span class="font-medium text-gray-800">{{ $requesterName }}</span></p>
                                                    @endif
                                                    @if($requesterDept)
                                                        <span class="px-2 py-0.5 rounded-full bg-gray-100 text-gray-700 text-[11px] border border-gray-200">
                                                            {{ $requesterDept }}
                                                        </span>
                                                    @endif
                                                </div>
                                                
                                                {{-- 5. Created Timestamp (Placed here to be near Requester info) --}}
                                                <div class="text-[10px] text-gray-500 mt-2">
                                                    Created: {{ optional($b->created_at)->timezone('Asia/Jakarta')->format('d M Y H:i') }}
                                                </div>

                                                {{-- Reject Note (if any) --}}
                                                @if($b->book_reject)
                                                    <div class="mt-2 text-xs text-amber-700 bg-amber-50 border border-amber-100 rounded-lg p-2">
                                                        <span class="font-medium">Note:</span> {{ $b->book_reject }}
                                                    </div>
                                                @endif
                                            </div>
                                            
                                        </div>
                                        
                                        {{-- 6. NEW: BOTTOM ACTIONS (Horizontally aligned, matching the image) --}}
                                        <div class="pt-3 border-t border-gray-100 flex justify-end gap-3">
                                            
                                            {{-- DETAIL BUTTON (Using ghost style as in the image) --}}
                                            <button type="button"
                                                wire:click="openDetailModal({{ $b->bookingroom_id }})"
                                                class="{{ $btnGhost }} border-gray-300 text-gray-700 hover:bg-gray-100 px-4 py-2">
                                                Detail
                                            </button>

                                            {{-- APPROVE BUTTON (Green) --}}
                                            <button type="button"
                                                wire:click="approve({{ $b->bookingroom_id }})"
                                                wire:loading.attr="disabled"
                                                wire:target="approve"
                                                class="px-4 py-2 text-xs font-medium rounded-lg bg-green-600 text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-600/20 disabled:opacity-60 transition inline-flex items-center justify-center">
                                                <x-heroicon-o-check class="w-3.5 h-3.5 inline-block mr-0.5"/>
                                                Approve
                                            </button>

                                            {{-- REJECT BUTTON (Red) --}}
                                            <button type="button"
                                                wire:click="openReject({{ $b->bookingroom_id }})"
                                                wire:loading.attr="disabled"
                                                wire:target="openReject"
                                                class="px-4 py-2 text-xs font-medium rounded-lg bg-rose-700 text-white hover:bg-rose-800 focus:outline-none focus:ring-2 focus:ring-rose-700/20 disabled:opacity-60 transition inline-flex items-center justify-center">
                                                <x-heroicon-o-x-mark class="w-3.5 h-3.5 inline-block mr-0.5"/>
                                                Reject
                                            </button>
                                        </div>
                                    </div>
                                    {{-- END: MODIFIED CARD DESIGN TO MATCH IMAGE --}}
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endif
                
                {{-- ONGOING TAB (Original code remains for ongoing tab) --}}
                @if($activeTab === 'ongoing')
                    @if($list->isEmpty())
                        <div class="px-4 sm:px-6 py-14 text-center text-gray-500 text-sm">
                            Tidak ada booking ongoing dengan filter saat ini.
                        </div>
                    @else
                        <div class="px-4 sm:px-6 py-5">
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                @foreach($list as $b)
                                    @php
                                        $isOnline   = in_array($b->booking_type, ['online_meeting','onlinemeeting']);
                                        $isRoomType = in_array($b->booking_type, ['bookingroom','meeting']);
                                        $avatarChar = strtoupper(substr($b->meeting_title ?? '—', 0, 1));

                                        $platform = $b->online_meeting_platform
                                                    ?? $b->platform
                                                    ?? $b->meeting_platform
                                                    ?? ($isOnline ? 'Online Meeting' : null);

                                        $meetingUrl      = $b->online_meeting_url ?? null;
                                        $meetingCode     = $b->online_meeting_code ?? null;
                                        $meetingPassword = $b->online_meeting_password ?? null;

                                        $requesterName = $b->user?->name
                                                            ?? $b->requester_name
                                                            ?? null;

                                        $requesterDept = $b->user?->department?->department_name
                                                            ?? $b->user?->department?->dept_name
                                                            ?? $b->department_name
                                                            ?? null;
                                    @endphp

                                    <div wire:key="ongoing-{{ $b->bookingroom_id }}"
                                        class="bg-white border border-gray-200 rounded-xl p-4 hover:shadow-sm hover:border-gray-300 transition">
                                        <div class="flex items-start gap-4">
                                            {{-- Avatar/Initial on the left --}}
                                            <div class="{{ $icoAvatar }} mt-0.5">{{ $b->meeting_title ? $avatarChar : '?' }}</div>
                                            
                                            <div class="flex-1 min-w-0">
                                                {{-- TOP ROW: Title, Type, Status --}}
                                                <div class="flex items-center justify-between gap-3 min-w-0 mb-2">
                                                    <h4 class="font-semibold text-gray-900 text-base truncate pr-2">
                                                        {{ $b->meeting_title ?? 'Untitled meeting' }}
                                                    </h4>
                                                    <div class="flex-shrink-0 flex items-center gap-2">
                                                        <span class="text-[11px] px-2 py-0.5 rounded-full border flex-shrink-0 {{ $isOnline ? 'border-emerald-300 text-emerald-700 bg-emerald-50' : 'border-blue-300 text-blue-700 bg-blue-50' }}">
                                                            {{ $isOnline ? 'ONLINE' : 'OFFLINE' }}
                                                        </span>
                                                        <span class="text-[11px] px-2 py-0.5 rounded-full bg-green-100 text-green-800 flex-shrink-0">
                                                            {{ strtoupper($b->status) }}
                                                        </span>
                                                    </div>
                                                </div>

                                                {{-- MIDDLE SECTION: Date, Time, Room --}}
                                                <div class="space-y-2 text-[13px] text-gray-600 mb-3 border-y border-gray-100 py-2">
                                                    <div class="flex items-center gap-5">
                                                        <span class="flex items-center gap-1.5 font-medium text-gray-800">
                                                            <x-heroicon-o-calendar class="w-4 h-4 text-gray-500"/>
                                                            {{ fmtDate($b->date) }}
                                                        </span>
                                                        <span class="flex items-center gap-1.5 font-medium text-gray-800">
                                                            <x-heroicon-o-clock class="w-4 h-4 text-gray-500"/>
                                                            {{ fmtTime($b->start_time) }}–{{ fmtTime($b->end_time) }}
                                                        </span>
                                                    </div>
                                                    @if($isRoomType)
                                                        <span class="{{ $chip }} text-xs px-2.5 py-0.5">
                                                            <x-heroicon-o-building-office class="w-3.5 h-3.5 text-gray-500"/>
                                                            <span class="font-medium text-gray-700">
                                                                Room: {{ $b->room?->room_name ?? '—' }}
                                                            </span>
                                                        </span>
                                                    @elseif($isOnline && $platform)
                                                        <span class="{{ $chip }} text-xs px-2.5 py-0.5 bg-emerald-50 border border-emerald-100 text-emerald-700">
                                                            <x-heroicon-o-folder class="w-3.5 h-3.5 text-emerald-500"/>
                                                            <span class="font-medium">{{ $platform }}</span>
                                                        </span>
                                                    @endif
                                                </div>

                                                {{-- BOTTOM LEFT: Requester Info --}}
                                                <div class="text-[12px] text-gray-600">
                                                    @if($requesterName)
                                                        <p class="mb-1">Requested by <span class="font-medium text-gray-800">{{ $requesterName }}</span></p>
                                                    @endif
                                                    @if($requesterDept)
                                                        <span class="px-2 py-0.5 rounded-full bg-gray-100 text-gray-700 text-[11px] border border-gray-200">
                                                            {{ $requesterDept }}
                                                        </span>
                                                    @endif
                                                </div>
                                                
                                                {{-- Reject Note (if any) --}}
                                                @if($b->book_reject)
                                                    <div class="mt-2 text-xs text-amber-700 bg-amber-50 border border-amber-100 rounded-lg p-2">
                                                        <span class="font-medium">Note:</span> {{ $b->book_reject }}
                                                    </div>
                                                @endif
                                            </div>
                                            
                                            {{-- RIGHT: Actions and Timestamp --}}
                                            <div class="text-right shrink-0 space-y-2 pt-0.5">
                                                <div class="flex flex-col gap-2 justify-end">
                                                    {{-- DETAIL BUTTON --}}
                                                    <button type="button"
                                                        wire:click="openDetailModal({{ $b->bookingroom_id }})"
                                                        class="{{ $btnGhost }} border-gray-300 bg text-gray-700 hover:bg-gray-100">
                                                        <x-heroicon-o-eye class="w-3.5 h-3.5 inline-block mr-0.5"/>
                                                        Detail
                                                    </button>

                                                    {{-- CANCEL BUTTON (for ongoing) --}}
                                                    <button type="button"
                                                        x-data
                                                        @click="
                                                            if (confirm('Are you sure you want to cancel this request?')) {
                                                                $wire.openReschedule({{ $b->bookingroom_id }});
                                                            }
                                                        "
                                                        wire:loading.attr="disabled"
                                                        wire:target="openReschedule"
                                                        class="px-3 py-2 text-xs font-medium rounded-lg bg-rose-600 text-white hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-600/20 disabled:opacity-60 transition inline-flex items-center justify-center">
                                                        <x-heroicon-o-x-mark class="w-3.5 h-3.5 inline-block mr-0.5"/>
                                                        Cancel
                                                    </button>
                                                </div>

                                                <span class="inline-block text-[10px] px-2 py-0.5 rounded-lg bg-gray-50 text-gray-500 border border-gray-200">
                                                    Created: {{ optional($b->created_at)->timezone('Asia/Jakarta')->format('d M Y H:i') }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endif

                {{-- PAGINATION --}}
                <div class="px-4 sm:px-6 py-5 bg-gray-50 border-top border-gray-200">
                    <div class="flex justify-center">
                        @if($activeTab === 'pending')
                            {{ $pending->onEachSide(1)->links() }}
                        @else
                            {{ $ongoing->onEachSide(1)->links() }}
                        @endif
                    </div>
                </div>
            </section>

            {{-- RIGHT: SIDEBAR (Rooms) --}}
            <aside class="hidden md:flex md:flex-col md:col-span-1 gap-4">
                <section class="{{ $card }}">
                    <div class="px-4 py-4 border-b border-gray-200">
                        <h3 class="text-sm font-semibold text-gray-900">Filter by Room</h3>
                        <p class="text-xs text-gray-500 mt-1">Klik salah satu ruangan untuk mem-filter daftar approval.</p>
                    </div>

                    <div class="px-4 py-3 max-h-64 overflow-y-auto">
                        {{-- All rooms --}}
                        <button type="button"
                            wire:click="clearRoomFilter"
                            class="w-full flex items-center justify-between gap-2 px-3 py-2 rounded-lg text-xs font-medium
                                {{ is_null($roomFilterId) ? 'bg-gray-900 text-white' : 'text-gray-800 hover:bg-gray-100' }}">
                            <span class="flex items-center gap-2">
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-md border border-gray-300 text-[11px]">
                                    All
                                </span>
                                <span>All Rooms</span>
                            </span>
                            @if(is_null($roomFilterId))
                                <span class="text[10px] uppercase tracking-wide opacity-80">Active</span>
                            @endif
                        </button>

                        <div class="mt-2 space-y-1.5">
                            @forelse($roomsOptions as $r)
                                @php $active = !is_null($roomFilterId) && (int)$roomFilterId === (int)$r['id']; @endphp
                                <button type="button"
                                    wire:click="selectRoom({{ $r['id'] }})"
                                    class="w-full flex items-center justify-between gap-2 px-3 py-2 rounded-lg text-xs
                                        {{ $active ? 'bg-gray-900 text-white' : 'text-gray-800 hover:bg-gray-100' }}">
                                    <span class="flex items-center gap-2">
                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-md border border-gray-300 text-[11px]">
                                            {{ substr($r['label'],0,2) }}
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

        {{-- REJECT MODAL (Alasan wajib) - REFACTORED AND FIXED TEXTAREA HEIGHT --}}
        @if($showRejectModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center"
            role="dialog" aria-modal="true"
            wire:key="reject-modal"
            wire:keydown.escape.window="closeReject">
            {{-- Backdrop --}}
            <button type="button" class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-opacity" aria-label="Close overlay" wire:click="closeReject"></button>

            <div class="relative w-full max-w-lg mx-4 bg-white rounded-2xl border border-gray-200 shadow-xl overflow-hidden focus:outline-none transform transition-all" tabindex="-1">

                <form wire:submit.prevent="confirmReject">
                    {{-- Modal Header (Like Detail Modal) --}}
                    <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <x-heroicon-o-x-circle class="w-5 h-5 text-rose-600" />
                            <h3 class="text-base font-semibold text-gray-900">Tolak Booking</h3>
                        </div>
                        <button class="text-gray-500 hover:text-gray-700" type="button" wire:click="closeReject" aria-label="Close">
                            <x-heroicon-o-x-mark class="w-5 h-5" />
                        </button>
                    </div>

                    {{-- Modal Body (Updated Padding) --}}
                    <div class="p-5 space-y-4">
                        <p class="text-sm text-gray-600">Berikan alasan penolakan. Field ini wajib diisi.</p>
                        <div>
                            <label class="{{ $label }}">Alasan penolakan <span class="text-rose-600">*</span></label>
                            {{-- Using $textareaInput or overriding the height in $input is necessary to fix height issue --}}
                            <textarea wire:model.live="rejectReason"
                                rows="4"
                                class="{{ $textareaInput }} resize-none" 
                                placeholder="Contoh: Jadwal bentrok dengan rapat lain / Ruangan tidak tersedia"
                                required></textarea>
                            @error('rejectReason')
                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Modal Footer (Like Detail Modal) --}}
                    <div class="bg-gray-50 px-5 py-4 border-t border-gray-200 flex items-center justify-end gap-2">
                        <button type="button" class="{{ $btnGhost }} inline-flex items-center gap-1.5" wire:click="closeReject" wire:loading.attr="disabled" wire:target="confirmReject">
                            <x-heroicon-o-arrow-uturn-left class="w-4 h-4" />
                            <span>Batal</span>
                        </button>
                        <button type="submit"
                            class="px-3 py-2 text-xs font-medium rounded-lg bg-rose-600 text-white hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-600/20 disabled:opacity-60 transition inline-flex items-center gap-1.5"
                            wire:loading.attr="disabled" wire:target="confirmReject">
                            <x-heroicon-o-x-mark class="w-4 h-4" />
                            <span>Konfirmasi Tolak</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endif

        {{-- RESCHEDULE MODAL --}}
        @if($showRescheduleModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="absolute inset-0 bg-black/40" wire:click="closeReschedule"></div>

            <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg mx-4">
                <form wire:submit.prevent="submitReschedule">
                    <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900">Reschedule Booking</h3>
                            <p class="text-xs text-gray-500">
                                Atur ulang tanggal, waktu, dan ruangan. Alasan reschedule wajib diisi.
                            </p>
                        </div>
                        <button type="button" class="text-gray-400 hover:text-gray-600" wire:click="closeReschedule">✕</button>
                    </div>

                    <div class="px-5 py-4 space-y-4">
                        <div>
                            <label class="{{ $label }}">Tanggal baru</label>
                            <input type="date" class="{{ $input }}" wire:model.live="rescheduleDate" required>
                            @error('rescheduleDate') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="{{ $label }}">Jam mulai</label>
                                <input type="time" class="{{ $input }}" wire:model.live="rescheduleStart" required>
                                @error('rescheduleStart') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="{{ $label }}">Jam selesai</label>
                                <input type="time" class="{{ $input }}" wire:model.live="rescheduleEnd" required>
                                @error('rescheduleEnd') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="{{ $label }}">Ruangan (opsional)</label>
                            <select class="{{ $input }}" wire:model.live="rescheduleRoomId">
                                <option value="">Pilih ruangan…</option>
                                @foreach($roomsOptions as $r)
                                <option value="{{ $r['id'] }}">{{ $r['label'] }}</option>
                                @endforeach
                            </select>
                            @error('rescheduleRoomId') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="{{ $label }}">Alasan reschedule <span class="text-rose-600">*</span></label>
                            <textarea rows="3" class="{{ $textareaInput }} resize-none" wire:model.live="rescheduleReason" required></textarea>
                            @error('rescheduleReason') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="px-5 py-3 border-t border-gray-200 flex items-center justify-end gap-2 bg-gray-50">
                        <button type="button" class="{{ $btnGhost }}" wire:click="closeReschedule" wire:loading.attr="disabled" wire:target="submitReschedule">
                            Batal
                        </button>
                        <button type="submit" class="{{ $btnBlk }}" wire:loading.attr="disabled" wire:target="submitReschedule">
                            Simpan Reschedule
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endif

        {{-- MOBILE FILTER MODAL --}}
        @if($showFilterModal)
        <div class="fixed inset-0 z-40 md:hidden">
            <div class="absolute inset-0 bg-black/40" wire:click="closeFilterModal"></div>
            <div class="absolute inset-x-0 bottom-0 bg-white rounded-t-2xl shadow-xl max-h-[80vh] overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">Filter & Recent</h3>
                        <p class="text-[11px] text-gray-500">Filter berdasarkan ruangan & lihat aktivitas terbaru.</p>
                    </div>
                </div>

                <div class="p-4 space-y-4 max-h-[70vh] overflow-y-auto">
                    <div>
                        <h4 class="text-xs font-semibold text-gray-800 mb-2">Filter by Room</h4>

                        <button type="button"
                            wire:click="clearRoomFilter"
                            class="w-full flex items-center justify-between gap-2 px-3 py-2 rounded-lg text-xs font-medium
                                {{ is_null($roomFilterId) ? 'bg-gray-900 text-white' : 'text-gray-800 hover:bg-gray-100' }}">
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
                                @php
                                    $active = !is_null($roomFilterId) && (int) $roomFilterId === (int) $r['id'];
                                @endphp
                                <button type="button"
                                    wire:click="selectRoom({{ $r['id'] }})"
                                    class="w-full flex items-center justify-between gap-2 px-3 py-2 rounded-lg text-xs
                                        {{ $active ? 'bg-gray-900 text-white' : 'text-gray-800 hover:bg-gray-100' }}">
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

                <div class="px-4 py-3 border-t border-gray-200">
                    <button type="button"
                        class="w-full h-10 rounded-xl bg-gray-900 text-white text-xs font-medium"
                        wire:click="closeFilterModal">
                        Apply & Close
                    </button>
                </div>
            </div>
        </div>
        @endif

        {{-- BOOKING DETAIL MODAL (NEW & FIXED) --}}
        @if ($showDetailModal && $selectedBookingDetail)
        <div
            class="fixed inset-0 z-[60] flex items-center justify-center"
            role="dialog" aria-modal="true"
            wire:key="detail-modal-{{ $selectedBookingDetail->bookingroom_id }}"
            wire:keydown.escape.window="closeDetailModal">
            <button type="button" class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-opacity" aria-label="Close overlay" wire:click="closeDetailModal"></button>

            <div class="relative w-full max-w-lg mx-4 bg-white rounded-2xl border border-gray-200 shadow-xl overflow-hidden focus:outline-none transform transition-all" tabindex="-1">

                {{-- Modal Header --}}
                <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-eye class="w-5 h-5 text-gray-700" />
                        <h3 class="text-base font-semibold text-gray-900">Detail Booking</h3>
                    </div>
                    <button class="text-gray-500 hover:text-gray-700" type="button" wire:click="closeDetailModal" aria-label="Close">
                        <x-heroicon-o-x-mark class="w-5 h-5" />
                    </button>
                </div>

                {{-- Modal Body --}}
                <div class="p-5 space-y-4">
                    @php
                        $detail = $selectedBookingDetail;
                        $isOnline   = in_array($detail->booking_type, ['online_meeting','onlinemeeting']);
                        $statusClass = [
                            'approved' => 'bg-green-100 text-green-800 ring-green-300',
                            'pending' => 'bg-amber-100 text-amber-800 ring-amber-300',
                            'rejected' => 'bg-rose-100 text-rose-800 ring-rose-300',
                            'completed' => 'bg-blue-100 text-blue-800 ring-blue-300',
                            'cancelled' => 'bg-gray-100 text-gray-800 ring-gray-300',
                        ];
                        $status = strtoupper($detail->status ?? 'Cancelled');
                        $mono = 'text-[10px] font-mono text-gray-500 bg-gray-100 px-2.5 py-1 rounded-md'; // Redefine for local scope

                        // DATA BUG CHECK: Check if special_notes looks like a raw ID array (e.g., "[1, 2]")
                        $isSpecialNotesBugged = preg_match('/^\[\s*\w+/', trim($detail->special_notes ?? ''));

                        // START: Requirement Workaround Logic
                        $buggedReqIds = [];
                        if ($isSpecialNotesBugged) {
                            // Attempt to extract and cast IDs from the string
                            try {
                                $data = json_decode($detail->special_notes, true);
                                if (is_array($data) && count($data) > 0 && is_numeric($data[0] ?? null)) {
                                    $buggedReqIds = array_map('intval', $data);
                                }
                            } catch (\Throwable) { /* ignore */ }
                        }

                        // Determine final list of requirements to display
                        $requirementsToDisplay = $detail->requirements->isNotEmpty() 
                            ? $detail->requirements->pluck('name')->toArray() 
                            : [];
                            
                        // Fallback: If requirements are empty, and we have bugged IDs, fetch the names now.
                        $loadedFromBugged = false;
                        if (empty($requirementsToDisplay) && !empty($buggedReqIds)) {
                            // NOTE: This is an expensive query! It should be temporary.
                            $requirementsToDisplay = Requirement::whereIn('id', $buggedReqIds)->pluck('name')->toArray();
                            if (!empty($requirementsToDisplay)) {
                                $loadedFromBugged = true;
                            }
                        }
                        // END: Requirement Workaround Logic
                    @endphp

                    {{-- Title and Status --}}
                    <div class="pb-2 border-b border-gray-100">
                        <h4 class="text-lg font-bold text-gray-900 mb-1">{{ $detail->meeting_title ?? 'Untitled Meeting' }}</h4>
                        <span class="{{ $chip }} {{ $statusClass[strtolower($detail->status ?? 'cancelled')] ?? 'bg-gray-100 text-gray-700 ring-gray-300' }}">
                            Status: {{ ucfirst(strtolower($status)) }}
                        </span>
                        <span class="{{ $mono }} ml-2">ID: {{ $detail->bookingroom_id }}</span>
                    </div>

                    <div class="divide-y divide-gray-100">

                        {{-- REQUIREMENT DETAILS (FIXED/WORKAROUND) --}}
                        @if (!empty($requirementsToDisplay))
                        <div class="{{ $detailItem }}">
                            <div class="text-xs font-medium text-gray-500 flex items-center gap-1.5 mb-2 pb-1 border-b border-gray-100">
                                <x-heroicon-o-check-badge class="w-4 h-4 text-gray-400" />
                                Daftar Kebutuhan:
                                @if ($loadedFromBugged)
                                    <span class="text-[10px] text-rose-600 font-semibold">(LOADED FROM BUGGED DATA)</span>
                                @endif
                            </div>

                            <div class="flex flex-wrap gap-2 mt-2">
                                @foreach ($requirementsToDisplay as $reqName)
                                <span class="{{ $chip }} bg-gray-100 text-gray-700 ring-gray-300">
                                    {{ $reqName ?? 'N/A' }}
                                </span>
                                @endforeach
                            </div>
                        </div>
                        @else
                            {{-- WARNING IF REQUIREMENTS ARE EMPTY BUT SPECIAL NOTES ARE BUGGED --}}
                            @if ($isSpecialNotesBugged)
                            <div class="{{ $detailItem }} bg-rose-50 border-rose-300 rounded-lg p-3 text-sm text-rose-700">
                                <p class="font-semibold flex items-center gap-2"><x-heroicon-o-exclamation-triangle class="w-4 h-4" /> BUG PENTING: Kebutuhan Tidak Tersimpan!</p>
                                <p class="text-xs mt-1">Sistem gagal memuat Daftar Kebutuhan. Data ID kebutuhan (misalnya [9, 7]) kemungkinan tersimpan di kolom 'Catatan Khusus Booking' di database. Mohon **koreksi logic penyimpanan booking Anda**.</p>
                            </div>
                            @endif
                        @endif


                        {{-- Date & Time --}}
                        <div class="{{ $detailItem }}">
                            <div class="text-xs font-medium text-gray-500 flex items-center gap-1.5 mb-1">
                                <x-heroicon-o-calendar class="w-4 h-4 text-gray-400" />
                                Waktu Booking
                            </div>
                            <p class="text-sm font-semibold text-gray-800">
                                {{ \Illuminate\Support\Carbon::parse($detail->date)->format('d M Y') }}
                                <span class="text-gray-400 mx-1">/</span>
                                {{ \Illuminate\Support\Carbon::parse($detail->start_time)->format('H:i') }} – {{ \Illuminate\Support\Carbon::parse($detail->end_time)->format('H:i') }}
                            </p>
                        </div>

                        {{-- Type Details --}}
                        <div class="{{ $detailItem }} grid grid-cols-2 gap-4">
                            <div>
                                <div class="text-xs font-medium text-gray-500 flex items-center gap-1.5 mb-1">
                                    <x-heroicon-o-user-group class="w-4 h-4 text-gray-400" /> Jumlah Peserta
                                </div>
                                <p class="text-sm font-semibold text-gray-800">{{ $detail->number_of_attendees }}</p>
                            </div>
                            @if (!$isOnline)
                            <div>
                                <div class="text-xs font-medium text-gray-500 flex items-center gap-1.5 mb-1">
                                    <x-heroicon-o-building-office-2 class="w-4 h-4 text-gray-400" /> Ruang Meeting
                                </div>
                                <p class="text-sm font-semibold text-gray-800">{{ $detail->room->room_name ?? '—' }}</p>
                            </div>
                            @else
                            <div>
                                <div class="text-xs font-medium text-gray-500 flex items-center gap-1.5 mb-1">
                                    <x-heroicon-o-swatch class="w-4 h-4 text-gray-400" /> Provider Online
                                </div>
                                <p class="text-sm font-semibold text-gray-800 capitalize">{{ str_replace('_', ' ', $detail->online_provider ?? '—') }}</p>
                            </div>
                            @endif
                        </div>

                        {{-- Online Specific Details --}}
                        @if ($isOnline)
                        <div class="{{ $detailItem }} grid grid-cols-2 gap-4">
                            <div>
                                <div class="text-xs font-medium text-gray-500 mb-1">Kode Meeting</div>
                                <p class="text-sm font-semibold text-gray-800">{{ $detail->online_meeting_code ?: '—' }}</p>
                            </div>
                            <div>
                                <div class="text-xs font-medium text-gray-500 mb-1">Password</div>
                                <p class="text-sm font-semibold text-gray-800">{{ $detail->online_meeting_password ?: '—' }}</p>
                            </div>
                        </div>

                        <div class="{{ $detailItem }}">
                            <div class="text-xs font-medium text-gray-500 mb-1 flex items-center gap-1.5">
                                <x-heroicon-o-link class="w-4 h-4 text-gray-400" /> Meeting URL
                            </div>
                            @if ($detail->online_meeting_url)
                            <a href="{{ $detail->online_meeting_url }}" target="_blank"
                                class="text-blue-600 hover:underline break-all text-sm">
                                {{ $detail->online_meeting_url }}
                            </a>
                            @else
                            <p class="text-sm text-gray-700">—</p>
                            @endif
                        </div>
                        @endif

                        {{-- Reject Note --}}
                        @if ($detail->book_reject)
                        <div class="pt-3 pb-3 border-b border-gray-100">
                            <div class="text-xs font-medium text-amber-600 mb-1 flex items-center gap-1.5">
                                <x-heroicon-o-exclamation-triangle class="w-4 h-4" /> Catatan Penolakan/Reschedule
                            </div>
                            <p class="text-sm text-amber-800 whitespace-pre-wrap">{{ $detail->book_reject }}</p>
                        </div>
                        @endif
                        
                        {{-- Special Notes (The field containing the bugged data) --}}
                        <div class="pt-3">
                            <div class="text-xs font-medium text-gray-500 mb-1 flex items-center gap-1.5">
                                <x-heroicon-o-document-text class="w-4 h-4 text-gray-400" /> Catatan Khusus Booking
                            </div>
                            <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $detail->special_notes ?: '—' }}</p>
                            
                            @if ($isSpecialNotesBugged && !$loadedFromBugged)
                                <p class="text-xs text-rose-500 mt-1">⚠️ Perhatian: Konten ini terlihat seperti data array/ID yang salah disimpan. Harusnya kosong atau berisi catatan teks biasa.</p>
                            @endif
                        </div>

                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="bg-gray-50 px-5 py-4 flex justify-end">
                    <button wire:click="closeDetailModal" type="button"
                        class="{{ $btnGhost }} inline-flex items-center gap-1.5">
                        <x-heroicon-o-x-mark class="w-4 h-4" />
                        <span>Tutup</span>
                    </button>
                </div>
            </div>
        </div>
        @endif
    </main>
</div>