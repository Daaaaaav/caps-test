@php
use Carbon\Carbon;

if (!function_exists('fmtDate')) {
    // Helper function to format date
    function fmtDate($v) {
        try { return $v ? Carbon::parse($v)->format('d M Y') : '—'; }
        catch (\Throwable) { return '—'; }
    }
}
if (!function_exists('fmtTime')) {
    // Helper function to format time (H:i format)
    function fmtTime($v) {
        try { return $v ? Carbon::parse($v)->format('H:i') : '—'; } // 10:00
        catch (\Throwable) {
            if (is_string($v)) {
                if (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $v)) {
                    // Handle datetime-local format
                    return Carbon::parse($v)->format('H:i');
                }
                if (preg_match('/^\d{2}:\d{2}/', $v)) return substr($v,0,5);
            }
            return '—';
        }
    }
}

/** @var int|null $departmentFilterId */
$departmentFilterId = $departmentFilterId ?? null;
/** @var int|null $roomFilterId */
$roomFilterId = $roomFilterId ?? null;

$card      = 'bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden';
$label     = 'block text-sm font-medium text-gray-700 mb-2';
$input     = 'w-full h-10 px-3 rounded-lg border border-gray-300 text-gray-800 placeholder:text-gray-400 focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10 bg-white transition';
$btnBlk    = 'px-3 py-2 text-xs font-medium rounded-lg bg-gray-900 text-white hover:bg-black focus:outline-none focus:ring-2 focus:ring-gray-900/20 disabled:opacity-60 transition';
$btnRed    = 'px-3 py-2 text-xs font-medium rounded-lg bg-rose-600 text-white hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-600/20 disabled:opacity-60 transition';
$btnLt     = 'px-3 py-2 text-xs font-medium rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-900/10 disabled:opacity-60 transition';
$chip      = 'inline-flex items-center gap-2 px-2.5 py-1 rounded-lg bg-gray-100 text-xs';
$icoAvatar = 'w-10 h-10 bg-gray-900 rounded-xl flex items-center justify-center text-white font-semibold text-sm shrink-0';
$mono = 'text-[10px] font-mono text-gray-500 bg-gray-100 px-2.5 py-1 rounded-md';

// Helper to determine booking type for status/chip display
if (!function_exists('isOnlineBooking')) {
    function isOnlineBooking($booking) {
        return in_array(strtolower($booking->booking_type ?? ''), ['online_meeting', 'onlinemeeting']);
    }
}
@endphp

<div class="min-h-screen bg-gray-50">
    <main class="px-4 sm:px-6 py-6 space-y-6">
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
                            <!-- Blade Icon Replacement: Calendar Days icon for HERO -->
                            <x-heroicon-o-calendar-days class="w-6 h-6 text-white" />
                        </div>
                        <div>
                            <h2 class="text-lg sm:text-xl font-semibold">Booking Room Management</h2>
                            <p class="text-sm text-white/80">
                            Cabang: <span
                                class="font-semibold">{{ optional(Auth::user()->company)->company_name ?? '-' }}</span>
                        </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        {{-- Show deleted toggle --}}
                        <label class="inline-flex items-center gap-2 text-sm text-white/90">
                            <input type="checkbox"
                                   wire:model.live="withTrashed"
                                   class="rounded border-white/30 bg-white/10 focus:ring-white/40">
                            <span>Show Deleted</span>
                        </label>
                        <a href="{{ route('superadmin.manageroom') }}" class="{{ $btnLt }} border-white/30 text-white/90 hover:bg-white/10">Go to Rooms</a>
                    </div>
                </div>
            </div>
        </div>

        @if (session()->has('success'))
        <div class="bg-white border border-gray-200 shadow-lg rounded-xl px-4 py-3 text-sm text-gray-800">
            {{ session('success') }}
        </div>
        @endif
        
        {{-- MOBILE FILTER BUTTON (Visible on small screens) --}}
        <div class="md:hidden">
            <div class="flex items-center justify-between gap-3 p-3 bg-white rounded-xl shadow-sm border border-gray-200">
                <p class="text-xs text-gray-600">
                    @if(!is_null($roomFilterId))
                        Filtered by **{{ $roomLookup[$roomFilterId] ?? 'Unknown Room' }}**
                    @elseif(!is_null($departmentFilterId))
                        Filtered by **{{ $deptLookup[$departmentFilterId] ?? 'Unknown Dept' }}**
                    @else
                        **{{ $typeScope === 'all' ? 'All Types' : ($typeScope === 'offline' ? 'Offline' : 'Online') }}**
                    @endif
                </p>
                <button type="button" class="{{ $btnBlk }} !h-8 !px-4 !py-0" wire:click="openFilterModal">
                    Filter
                </button>
            </div>
        </div>

        {{-- MAIN LAYOUT (2 Columns) --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">

            {{-- LEFT: BOOKING LIST (MAIN AREA) --}}
            <section class="md:col-span-3 space-y-4">
                <div class="{{ $card }}">
                    {{-- Header: tabs & type scope --}}
                    <div class="px-4 sm:px-6 pt-4 pb-3 border-b border-gray-200 space-y-3">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div>
                                <h3 class="text-base font-semibold text-gray-900">All Bookings</h3>
                                <p class="text-xs text-gray-500">
                                    Manage active and completed bookings.
                                </p>
                            </div>
    
                            {{-- Tabs (Status Filters) --}}
                            <div class="inline-flex items-center bg-gray-100 rounded-full p-1 text-xs font-medium">
                                <button type="button"
                                        wire:click="setTab('all')"
                                        class="px-3 py-1 rounded-full transition {{ $activeTab === 'all' ? 'bg-gray-900 text-white shadow-sm' : 'text-gray-700 hover:bg-gray-200' }}">
                                    All
                                </button>
                                <button type="button" wire:click="setTab('done')" class="px-3 py-1 rounded-full transition {{ $activeTab === 'done' ? 'bg-gray-900 text-white shadow-sm' : 'text-gray-700 hover:bg-gray-200' }}">Done</button>
                                <button type="button" wire:click="setTab('rejected')" class="px-3 py-1 rounded-full transition {{ $activeTab === 'rejected' ? 'bg-gray-900 text-white shadow-sm' : 'text-gray-700 hover:bg-gray-200' }}">Rejected</button>
                                
                            </div>
                        </div>

                        {{-- Type scope (Online/Offline) --}}
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-end gap-2 text-xs mt-1">
                            <div class="inline-flex items-center bg-gray-100 rounded-full p-1 text-[11px] font-medium">
                                <button type="button"
                                        wire:click="setTypeScope('all')"
                                        class="px-3 py-1 rounded-full transition
                                            {{ $typeScope === 'all' ? 'bg-gray-900 text-white shadow-sm' : 'text-gray-700 hover:bg-gray-200' }}">
                                    All Types
                                </button>
                                <button type="button"
                                        wire:click="setTypeScope('offline')"
                                        class="px-3 py-1 rounded-full transition
                                            {{ $typeScope === 'offline' ? 'bg-gray-900 text-white shadow-sm' : 'text-gray-700 hover:bg-gray-200' }}">
                                    Offline (Room)
                                </button>
                                <button type="button"
                                        wire:click="setTypeScope('online')"
                                        class="px-3 py-1 rounded-full transition
                                            {{ $typeScope === 'online' ? 'bg-gray-900 text-white shadow-sm' : 'text-gray-700 hover:bg-gray-200' }}">
                                    Online Meeting
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- FILTERS --}}
                    <div class="px-4 sm:px-6 pt-4 pb-3 border-b border-gray-200">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="{{ $label }}">Search Title/Notes</label>
                                <div class="relative">
                                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search title or notes..."
                                        class="{{ $input }} pl-10 w-full placeholder:text-gray-400">
                                    <!-- Blade Icon Replacement: Magnifying Glass icon -->
                                    <x-heroicon-o-magnifying-glass class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                                </div>
                            </div>
                            <div>
                                <label class="{{ $label }}">Filter by Date</label>
                                <div class="relative">
                                    <input type="date" class="{{ $input }} pl-10" wire:model.live="selectedDate">
                                    <!-- Blade Icon Replacement: Calendar icon -->
                                    <x-heroicon-o-calendar class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                                </div>
                            </div>
                            <div>
                                <label class="{{ $label }}">Sort & Per Page</label>
                                <div class="flex gap-2">
                                    <select wire:model.live="dateMode" class="{{ $input }}">
                                        <option value="semua">Default Sort</option>
                                        <option value="terbaru">Date Newest</option>
                                        <option value="terlama">Date Oldest</option>
                                    </select>
                                    <select wire:model.live="perPage" class="{{ $input }} w-24">
                                        <option value="10">10</option>
                                        <option value="20">20</option>
                                        <option value="50">50</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- LIST AREA (2 cards per row) --}}
                    <div class="px-4 sm:px-6 py-5">
                        @if($bookings->isEmpty())
                            <div class="py-14 text-center text-gray-500 text-sm">No bookings found matching your criteria.</div>
                        @else
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                @foreach ($bookings as $b)
                                    @php
                                        $rowNo = (($bookings->firstItem() ?? 1) + $loop->index);
                                        $avatarChar = strtoupper(substr($b->meeting_title ?? '—', 0, 1));
                                        $reqs = $requirementsMap[$b->bookingroom_id] ?? [];
                                        $isOnline = isOnlineBooking($b);
                                        $statusBadge = '';
                                        $status = strtolower($b->status ?? '');
                                        
                                        if ($status == 'rejected') {
                                            $statusBadge = '<span class="text-[11px] px-2 py-0.5 rounded-full bg-red-100 text-red-800">Rejected</span>';
                                        } elseif ($status == 'completed') {
                                            $statusBadge = '<span class="text-[11px] px-2 py-0.5 rounded-full bg-green-100 text-green-800">Done</span>';
                                        } else {
                                            $statusBadge = '<span class="text-[11px] px-2 py-0.5 rounded-full bg-yellow-100 text-yellow-800">Active</span>';
                                        }
                                    @endphp
                                    <div wire:key="br-{{ $b->bookingroom_id }}"
                                         class="bg-white border border-gray-200 rounded-xl px-4 sm:px-5 py-4 hover:shadow-sm hover:border-gray-300 transition">
                                        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                                            {{-- LEFT: Info --}}
                                            <div class="flex items-start gap-3 flex-1">
                                                <div class="{{ $icoAvatar }}">{{ $avatarChar }}</div>
                                                <div class="min-w-0 flex-1">
                                                    <div class="flex flex-wrap items-center gap-2 mb-1.5">
                                                        <h4 class="font-semibold text-gray-900 text-base truncate">
                                                            {{ $b->meeting_title }}
                                                        </h4>
                                                        {{-- Type Badge --}}
                                                        <span class="text-[11px] px-2 py-0.5 rounded-full border {{ $isOnline ? 'border-emerald-300 text-emerald-700 bg-emerald-50' : 'border-blue-300 text-blue-700 bg-blue-50' }}">
                                                            {{ $isOnline ? 'Online Meeting' : 'Offline Room' }}
                                                        </span>
                                                        {!! $statusBadge !!}
                                                        @if($b->deleted_at)
                                                            <span class="text-[11px] px-2 py-0.5 rounded-full bg-rose-100 text-rose-800">Deleted</span>
                                                        @endif
                                                    </div>

                                                    <p class="text-[12px] text-gray-500 mb-2">
                                                        By: <span class="font-medium text-gray-700">{{ $b->user_full_name ?: '—' }}</span>
                                                    </p>

                                                    <div class="flex flex-wrap items-center gap-2 text-[13px] text-gray-600">
                                                        <span class="flex items-center gap-1.5">
                                                            <!-- Blade Icon Replacement: Calendar icon -->
                                                            <x-heroicon-o-calendar class="w-4 h-4" />
                                                            {{ fmtDate($b->date) }}
                                                        </span>
                                                        <span class="flex items-center gap-1.5">
                                                            <!-- Blade Icon Replacement: Clock icon -->
                                                            <x-heroicon-o-clock class="w-4 h-4" />
                                                            {{ fmtTime($b->start_time) }}–{{ fmtTime($b->end_time) }}
                                                        </span>
                                                    </div>

                                                    <div class="flex flex-wrap items-center gap-2 mt-2">
                                                        @if(!$isOnline)
                                                            <span class="{{ $chip }}"><span class="text-gray-500">Room:</span><span class="font-medium text-gray-700">{{ $roomLookup[$b->room_id] ?? '—' }}</span></span>
                                                        @endif
                                                        <span class="{{ $chip }}"><span class="text-gray-500">Dept:</span><span class="font-medium text-gray-700">{{ $deptLookup[$b->department_id] ?? '—' }}</span></span>
                                                        <span class="{{ $chip }}"><span class="text-gray-500">Att:</span><span class="font-medium text-gray-700">{{ $b->number_of_attendees }}</span></span>
                                                    </div>

                                                    {{-- Requirements chips --}}
                                                    @if(count($reqs))
                                                    <div class="flex flex-wrap gap-2 mt-2">
                                                        @foreach($reqs as $rname)
                                                        <span class="{{ $chip }} bg-gray-200 text-[11px]">
                                                            <span class="text-gray-600">Req:</span>
                                                            <span class="font-medium text-gray-700">{{ $rname }}</span>
                                                        </span>
                                                        @endforeach
                                                    </div>
                                                    @endif

                                                    @if($b->special_notes)
                                                        <div class="mt-2 text-xs text-gray-600">
                                                            <span class="font-medium">Notes:</span> {{ \Illuminate\Support\Str::limit($b->special_notes, 100) }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>

                                            {{-- RIGHT: actions --}}
                                            <div class="text-right shrink-0 space-y-2">
                                                <div class="{{ $mono }}">No. {{ $rowNo }}</div>
                                                <div class="flex flex-wrap gap-2 justify-end pt-1">
                                                    {{-- Note: Ensure openEdit is public and takes an integer argument --}}
                                                    <button class="{{ $btnBlk }}"
                                                        wire:click="openEdit({{ (int) $b->bookingroom_id }})"
                                                        wire:loading.attr="disabled"
                                                        wire:target="openEdit({{ (int) $b->bookingroom_id }})">
                                                        <span wire:loading.remove wire:target="openEdit({{ (int) $b->bookingroom_id }})">Edit</span>
                                                        <span wire:loading wire:target="openEdit({{ (int) $b->bookingroom_id }})">Loading…</span>
                                                    </button>

                                                    @if(!$b->deleted_at)
                                                        <button class="{{ $btnRed }}"
                                                            wire:click="delete({{ (int) $b->bookingroom_id }})"
                                                            onclick="return confirm('Soft delete this booking (move to trash)?')"
                                                            wire:loading.attr="disabled"
                                                            wire:target="delete({{ (int) $b->bookingroom_id }})">
                                                            <span wire:loading.remove wire:target="delete({{ (int) $b->bookingroom_id }})">Delete</span>
                                                            <span wire:loading wire:target="delete({{ (int) $b->bookingroom_id }})">Deleting…</span>
                                                        </button>
                                                    @else
                                                        <button class="{{ $btnBlk }} !bg-emerald-600 hover:!bg-emerald-700"
                                                            wire:click="restore({{ (int) $b->bookingroom_id }})"
                                                            wire:loading.attr="disabled"
                                                            wire:target="restore({{ (int) $b->bookingroom_id }})">
                                                            <span wire:loading.remove wire:target="restore({{ (int) $b->bookingroom_id }})">Restore</span>
                                                            <span wire:loading wire:target="restore({{ (int) $b->bookingroom_id }})">Restoring…</span>
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    @if($bookings->hasPages())
                    <div class="px-5 py-4 bg-gray-50 border-t border-gray-200">
                        <div class="flex justify-center">
                            {{ $bookings->withQueryString()->links() }}
                        </div>
                    </div>
                    @endif
                </div>
            </section>

            {{-- RIGHT: SIDEBAR (DEPARTMENT & ROOM FILTERS - Hidden on mobile) --}}
            <aside class="hidden md:flex md:flex-col md:col-span-1 gap-4">
                
                {{-- DEPARTMENT FILTER --}}
                <section class="{{ $card }}">
                    <div class="px-4 py-4 border-b border-gray-200">
                        <h3 class="text-sm font-semibold text-gray-900">Filter by Department</h3>
                        <p class="text-xs text-gray-500 mt-1">Filter list based on the department that made the booking.</p>
                    </div>

                    <div class="px-4 py-3 max-h-96 overflow-y-auto">
                        {{-- All Departments --}}
                        <button type="button"
                                wire:click="clearDepartmentFilter"
                                class="w-full flex items-center justify-between gap-2 px-3 py-2 rounded-lg text-xs font-medium
                                    {{ is_null($departmentFilterId) ? 'bg-gray-900 text-white' : 'text-gray-800 hover:bg-gray-100' }}">
                            <span class="flex items-center gap-2">
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-md border border-gray-300 text-[11px]">
                                    All
                                </span>
                                <span>All Departments ({{ count($deptOptions) }})</span>
                            </span>
                            @if(is_null($departmentFilterId))
                                <span class="text-[10px] uppercase tracking-wide opacity-80">Active</span>
                            @endif
                        </button>

                        {{-- Each Department --}}
                        <div class="mt-2 space-y-1.5">
                            @forelse($deptOptions as $d)
                                @php $active = !is_null($departmentFilterId) && (int) $departmentFilterId === (int) $d['id']; @endphp
                                <button type="button"
                                        wire:click="selectDepartment({{ $d['id'] }})"
                                        class="w-full flex items-center justify-between gap-2 px-3 py-2 rounded-lg text-xs
                                            {{ $active ? 'bg-gray-900 text-white' : 'text-gray-800 hover:bg-gray-100' }}">
                                    <span class="flex items-center gap-2">
                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-md border border-gray-300 text-[11px]">
                                            {{ substr($d['label'], 0, 2) }}
                                        </span>
                                        <span class="truncate">{{ $d['label'] }}</span>
                                    </span>
                                    @if($active)
                                        <span class="text-[10px] uppercase tracking-wide opacity-80">Active</span>
                                    @endif
                                </button>
                            @empty
                                <p class="text-xs text-gray-500">No department data found.</p>
                            @endforelse
                        </div>
                    </div>
                </section>
                
                {{-- ROOM FILTER (NEW) --}}
                <section class="{{ $card }}">
                    <div class="px-4 py-4 border-b border-gray-200">
                        <h3 class="text-sm font-semibold text-gray-900">Filter by Room (Offline)</h3>
                        <p class="text-xs text-gray-500 mt-1">Filter list to show only **offline bookings** for a specific room.</p>
                    </div>

                    <div class="px-4 py-3 max-h-96 overflow-y-auto">
                        {{-- All Rooms --}}
                        <button type="button"
                                wire:click="clearRoomFilter"
                                class="w-full flex items-center justify-between gap-2 px-3 py-2 rounded-lg text-xs font-medium
                                    {{ is_null($roomFilterId) ? 'bg-gray-900 text-white' : 'text-gray-800 hover:bg-gray-100' }}">
                            <span class="flex items-center gap-2">
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-md border border-gray-300 text-[11px]">
                                    All
                                </span>
                                <span>All Rooms ({{ count($roomsOptions) }})</span>
                            </span>
                            @if(is_null($roomFilterId))
                                <span class="text-[10px] uppercase tracking-wide opacity-80">Active</span>
                            @endif
                        </button>

                        {{-- Each Room --}}
                        <div class="mt-2 space-y-1.5">
                            @forelse($roomsOptions as $r)
                                @php $active = !is_null($roomFilterId) && (int) $roomFilterId === (int) $r['id']; @endphp
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
                                <p class="text-xs text-gray-500">No room data found.</p>
                            @endforelse
                        </div>
                    </div>
                </section>
            </aside>
        </div>


        {{-- MOBILE FILTER MODAL (NEW) --}}
        @if($showFilterModal)
            <div class="fixed inset-0 z-[60] md:hidden">
                <div class="absolute inset-0 bg-black/50" wire:click="closeFilterModal"></div>
                <div class="absolute inset-x-0 bottom-0 bg-white rounded-t-2xl shadow-xl max-h-[80vh] overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900">Filter Settings</h3>
                            <p class="text-[11px] text-gray-500">Filter daftar booking berdasarkan ruangan atau departemen.</p>
                        </div>
                        <button class="text-gray-500 hover:text-gray-700" type="button" wire:click="closeFilterModal" aria-label="Close">
                            <!-- Blade Icon Replacement: Close/X icon -->
                            <x-heroicon-o-x-mark class="w-5 h-5" />
                        </button>
                    </div>

                    <div class="p-4 space-y-5 max-h-[70vh] overflow-y-auto">
                        
                        {{-- Filter by Room (Mobile) --}}
                        <div>
                            <h4 class="text-sm font-semibold text-gray-800 mb-2 border-b pb-1">Filter by Room</h4>

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
                                    @php $active = !is_null($roomFilterId) && (int) $roomFilterId === (int) $r['id']; @endphp
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

                        {{-- Filter by Department (Mobile) --}}
                        <div>
                            <h4 class="text-sm font-semibold text-gray-800 mb-2 border-b pb-1">Filter by Department</h4>

                            <button type="button"
                                    wire:click="clearDepartmentFilter"
                                    class="w-full flex items-center justify-between gap-2 px-3 py-2 rounded-lg text-xs font-medium
                                        {{ is_null($departmentFilterId) ? 'bg-gray-900 text-white' : 'text-gray-800 hover:bg-gray-100' }}">
                                <span class="flex items-center gap-2">
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-md border border-gray-300 text-[11px]">
                                        All
                                    </span>
                                    <span>All Departments</span>
                                </span>
                                @if(is_null($departmentFilterId))
                                    <span class="text-[10px] uppercase tracking-wide opacity-80">Active</span>
                                @endif
                            </button>

                            <div class="mt-2 space-y-1.5">
                                @forelse($deptOptions as $d)
                                    @php $active = !is_null($departmentFilterId) && (int) $departmentFilterId === (int) $d['id']; @endphp
                                    <button type="button"
                                            wire:click="selectDepartment({{ $d['id'] }})"
                                            class="w-full flex items-center justify-between gap-2 px-3 py-2 rounded-lg text-xs
                                                {{ $active ? 'bg-gray-900 text-white' : 'text-gray-800 hover:bg-gray-100' }}">
                                        <span class="flex items-center gap-2">
                                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-md border border-gray-300 text-[11px]">
                                                {{ substr($d['label'], 0, 2) }}
                                            </span>
                                            <span class="truncate">{{ $d['label'] }}</span>
                                        </span>
                                        @if($active)
                                            <span class="text-[10px] uppercase tracking-wide opacity-80">Active</span>
                                        @endif
                                    </button>
                                @empty
                                    <p class="text-xs text-gray-500">Tidak ada data departemen.</p>
                                @endforelse
                            </div>
                        </div>

                    </div>

                    <div class="px-4 py-3 border-t border-gray-200">
                        <button type="button"
                                class="w-full h-10 rounded-xl bg-gray-900 text-white text-sm font-medium"
                                wire:click="closeFilterModal">
                            Apply & Close
                        </button>
                    </div>
                </div>
            </div>
        @endif


        {{-- MODAL: EDIT ONLY (z-index is high to ensure it sits on top of the mobile filter) --}}
        @if($modal)
        <div class="fixed inset-0 z-[70] flex items-center justify-center" role="dialog" aria-modal="true" wire:key="modal-br" wire:keydown.escape.window="closeModal">
            <button type="button" class="absolute inset-0 bg-black/50" aria-label="Close overlay" wire:click="closeModal"></button>

            <div class="relative w-full max-w-2xl mx-4 {{ $card }} focus:outline-none" tabindex="-1">
                <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-base font-semibold text-gray-900">Edit Booking #{{ $editingId }}</h3>
                    <button class="text-gray-500 hover:text-gray-700" type="button" wire:click="closeModal" aria-label="Close">
                        <!-- Blade Icon Replacement: Close/X icon -->
                        <x-heroicon-o-x-mark class="w-5 h-5" />
                    </button>
                </div>

                <form class="p-5" wire:submit.prevent="update">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="{{ $label }}">Room</label>
                            <select class="{{ $input }}" wire:model.defer="room_id">
                                <option value="">Choose room</option>
                                @foreach ($roomLookup as $rid => $rname)
                                <option value="{{ $rid }}">{{ $rname }}</option>
                                @endforeach
                            </select>
                            @error('room_id') <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="{{ $label }}">Department</label>
                            <select class="{{ $input }}" wire:model.defer="department_id">
                                <option value="">Choose department</option>
                                @foreach ($deptLookup as $did => $dname)
                                <option value="{{ $did }}">{{ $dname }}</option>
                                @endforeach
                            </select>
                            @error('department_id') <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p> @enderror
                        </div>
                        <div class="md:col-span-2">
                            <label class="{{ $label }}">Meeting Title</label>
                            <input type="text" class="{{ $input }}" wire:model.defer="meeting_title" placeholder="Weekly Sync / Project Kickoff">
                            @error('meeting_title') <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="{{ $label }}">Date</label>
                            <input type="date" class="{{ $input }}" wire:model.defer="date">
                            @error('date') <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="{{ $label }}">Attendees</label>
                            <input type="number" min="1" class="{{ $input }}" wire:model.defer="number_of_attendees" placeholder="e.g. 10">
                            @error('number_of_attendees') <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="{{ $label }}">Start Time</label>
                            <input type="datetime-local" class="{{ $input }}" wire:model.defer="start_time">
                            @error('start_time') <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="{{ $label }}">End Time</label>
                            <input type="datetime-local" class="{{ $input }}" wire:model.defer="end_time">
                            @error('end_time') <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p> @enderror
                        </div>
                        <div class="md:col-span-2">
                            <label class="{{ $label }}">Special Notes (optional)</label>
                            <textarea class="{{ $input }} h-24" wire:model.defer="special_notes" placeholder="Agenda, equipment needs, etc."></textarea>
                            @error('special_notes') <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p> @enderror
                        </div>

                        {{-- Requirements checklist --}}
                        <div class="md:col-span-2">
                            <label class="{{ $label }}">Requirements</label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mt-1">
                                @foreach ($allRequirements as $req)
                                <label class="flex items-center space-x-2 text-sm text-gray-700">
                                    <input type="checkbox"
                                        wire:model.defer="selectedRequirements"
                                        value="{{ $req->requirement_id }}"
                                        class="rounded border-gray-300 text-gray-900 focus:ring-gray-900/30">
                                    <span>{{ $req->name }}</span>
                                </label>
                                @endforeach
                            </div>
                            @error('selectedRequirements') <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-3 border-t border-gray-200 pt-4">
                        <button type="button" class="{{ $btnLt }}" wire:click="closeModal">Cancel</button>
                        <button type="submit" class="{{ $btnBlk }}" wire:loading.attr="disabled" wire:target="update">
                            <span wire:loading.remove wire:target="update">Update Booking</span>
                            <span class="inline-flex items-center gap-2" wire:loading wire:target="update">
                                <!-- Blade Icon Replacement: Loading/Spin icon -->
                                <x-heroicon-o-arrow-path class="animate-spin h-4 w-4" />
                                Processing…
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endif
    </main>
</div>