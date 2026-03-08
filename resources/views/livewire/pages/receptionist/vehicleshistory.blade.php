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

    $card  = 'bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden';
    $label = 'block text-sm font-medium text-gray-700 mb-2';
    $input = 'w-full h-10 px-3 rounded-lg border border-gray-300 text-gray-800 placeholder:text-gray-400 focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10 bg-white transition';
    $chip  = 'inline-flex items-center gap-2 px-2.5 py-1 rounded-lg bg-gray-100 text-xs';
    $icoAvatar = 'w-10 h-10 bg-gray-900 rounded-xl flex items-center justify-center text-white font-semibold text-sm shrink-0';
@endphp

<div class="min-h-screen bg-gray-50">
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
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg sm:text-xl font-semibold">Vehicle History</h2>
                            <p class="text-sm text-white/80">
                                {{ $statusTab === 'rejected'
                                    ? 'Riwayat peminjaman yang ditolak (Rejected).'
                                    : 'Riwayat peminjaman kendaraan yang sudah Completed (Done).' }}
                            </p>
                        </div>
                    </div>

                    {{-- Include Deleted --}}
                    <label class="inline-flex items-center gap-2 text-sm text-white/90 cursor-pointer">
                        <input type="checkbox" wire:model.live="includeDeleted"
                            class="w-4 h-4 rounded border-white/30 bg-white/10 text-gray-900 focus:ring-2 focus:ring-white/20 cursor-pointer">
                        <span>Include Deleted</span>
                    </label>
                </div>
            </div>
        </div>

        {{-- MAIN GRID --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">

            {{-- LIST --}}
            <section class="{{ $card }} md:col-span-3">

                {{-- Header --}}
                <div class="px-4 sm:px-6 pt-4 pb-3 border-b border-gray-200 space-y-3">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900">Vehicle History</h3>
                            <p class="text-xs text-gray-500">
                                {{ $statusTab === 'rejected'
                                    ? 'Riwayat peminjaman kendaraan yang ditolak (Rejected).'
                                    : 'Riwayat peminjaman kendaraan yang telah selesai (Done / Completed).' }}
                            </p>
                        </div>

                        {{-- Tabs --}}
                        <div class="inline-flex items-center bg-gray-100 rounded-full p-1 text-xs font-medium">
                            <button type="button" wire:click="$set('statusTab','done')"
                                class="px-3 py-1 rounded-full transition {{ $statusTab === 'done' ? 'bg-gray-900 text-white shadow-sm' : 'text-gray-700 hover:bg-gray-200' }}">
                                Done
                            </button>
                            <button type="button" wire:click="$set('statusTab','rejected')"
                                class="px-3 py-1 rounded-full transition {{ $statusTab === 'rejected' ? 'bg-gray-900 text-white shadow-sm' : 'text-gray-700 hover:bg-gray-200' }}">
                                Rejected
                            </button>
                        </div>
                    </div>

                    {{-- Filter Indicator --}}
                    <div class="flex flex-wrap items-center gap-2 text-xs mt-1">
                        @if(!is_null($vehicleFilter))
                            @php $activeVehicle = $vehicleMap[$vehicleFilter] ?? 'Unknown'; @endphp
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-gray-900 text-white border border-gray-800">
                                Vehicle: {{ $activeVehicle }}
                                <button type="button" class="ml-1 hover:text-gray-200" wire:click="$set('vehicleFilter', null)">×</button>
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-gray-100 text-gray-700 border border-dashed border-gray-300">
                                No vehicle filter
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Filters --}}
                <div class="px-4 sm:px-6 pt-4 pb-3 border-b border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="{{ $label }}">Search</label>
                            <div class="relative">
                                <input type="text" class="{{ $input }} pl-9"
                                    placeholder="Search purpose, destination, borrower…"
                                    wire:model.live.debounce.400ms="q">
                                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="m21 21-4.3-4.3M10 18a8 8 0 1 1 0-16 8 8 0 0 1 0 16z" />
                                </svg>
                            </div>
                        </div>

                        <div>
                            <label class="{{ $label }}">Tanggal</label>
                            <input type="date" wire:model.live="selectedDate" class="{{ $input }}">
                        </div>

                        <div>
                            <label class="{{ $label }}">Urutkan</label>
                            <select wire:model.live="sortFilter" class="{{ $input }}">
                                <option value="recent">Default (terbaru)</option>
                                <option value="oldest">Terlama dulu</option>
                                <option value="nearest">Paling dekat sekarang</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- LIST BODY – 2 column bento style --}}
                @if($bookings->isEmpty())
                    <div class="px-4 sm:px-6 py-14 text-center text-gray-500 text-sm">
                        Belum ada riwayat untuk filter ini.
                    </div>
                @else
                <div class="px-4 sm:px-6 py-5">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        @foreach($bookings as $b)
                            @php
                                $vehicleName = $vehicleMap[$b->vehicle_id] ?? 'Unknown';
                                $avatarChar = strtoupper(substr($vehicleName,0,1));
                                $isRejected = $b->status === 'rejected';
                                $isTrashed  = method_exists($b, 'trashed') ? $b->trashed() : false;
                                $statusStyle = $isRejected
                                    ? ['bg'=>'bg-rose-100','text'=>'text-rose-800','label'=>'Rejected']
                                    : ['bg'=>'bg-emerald-100','text'=>'text-emerald-800','label'=>'Completed'];
                            @endphp
                            
                            {{-- START: MODIFIED VEHICLE HISTORY CARD DESIGN --}}
                            <div wire:key="history-{{ $b->vehiclebooking_id }}"
                                class="bg-white border border-gray-200 rounded-xl p-4 space-y-3 hover:shadow-sm hover:border-gray-300 transition">
                                
                                <div class="flex items-start gap-4">
                                    {{-- 1. Avatar/Initial on the left --}}
                                    <div class="{{ $icoAvatar }} mt-0.5">{{ $avatarChar }}</div>
                                    
                                    <div class="flex-1 min-w-0">
                                        {{-- 2. TOP ROW: Title, Status, ID --}}
                                        <div class="flex items-center justify-between gap-3 min-w-0 mb-2">
                                            <h4 class="font-semibold text-gray-900 text-base truncate pr-2">
                                                {{ $b->purpose ? ucfirst($b->purpose) : 'Vehicle Booking' }}
                                            </h4>
                                            <div class="flex-shrink-0 flex items-center gap-2">
                                                {{-- Status Badge --}}
                                                <span class="text-[11px] px-2 py-0.5 rounded-full flex-shrink-0 {{ $statusStyle['bg'] }} {{ $statusStyle['text'] }}">
                                                    {{ $statusStyle['label'] }}
                                                </span>
                                                @if($isTrashed)
                                                    <span class="text-[11px] px-2 py-0.5 rounded-full bg-gray-200 text-gray-800 border border-gray-300 flex-shrink-0">
                                                        Deleted
                                                    </span>
                                                @endif
                                                {{-- ID Chip --}}
                                                <span class="text-[11px] px-2 py-0.5 rounded-full border border-gray-300 text-gray-700 bg-gray-50 flex-shrink-0">
                                                    #{{ $b->vehiclebooking_id }}
                                                </span>
                                            </div>
                                        </div>

                                        {{-- 3. MIDDLE SECTION: Vehicle, Date, Time --}}
                                        <div class="space-y-2 text-[13px] text-gray-600 mb-3 border-y border-gray-100 py-2">
                                            
                                            {{-- Vehicle Name Chip --}}
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span class="{{ $chip }} text-xs px-2.5 py-0.5 bg-gray-100">
                                                    <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                              d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7" />
                                                    </svg>
                                                    <span class="font-medium text-gray-700">{{ $vehicleName }}</span>
                                                </span>
                                            </div>

                                            {{-- Dates and Times --}}
                                            <div class="flex flex-wrap items-center gap-4">
                                                <span class="flex items-center gap-1.5 font-medium text-gray-800">
                                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                    </svg>
                                                    {{ fmtDate($b->start_at) }}
                                                </span>
                                                <span class="flex items-center gap-1.5 font-medium text-gray-800">
                                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    {{ fmtTime($b->start_at) }}–{{ fmtTime($b->end_at) }}
                                                </span>
                                            </div>
                                        </div>

                                        {{-- 4. BOTTOM LEFT: Borrower & Notes/Timestamp --}}
                                        <div class="text-[12px] text-gray-600 space-y-1">
                                            @if(!empty($b->borrower_name))
                                                <p>Borrower: <span class="font-medium text-gray-800">{{ $b->borrower_name }}</span></p>
                                            @endif
                                            <span class="inline-block text-[10px] text-gray-500 mt-1">
                                                Created: {{ optional($b->created_at)->timezone('Asia/Jakarta')->format('d M Y H:i') }}
                                            </span>
                                        </div>

                                        {{-- Rejected Note --}}
                                        @if($isRejected && !empty($b->notes))
                                            <div class="mt-2 text-xs text-rose-700 bg-rose-50 border border-rose-100 rounded-lg p-2">
                                                <span class="font-medium">Reject Reason:</span> {{ $b->notes }}
                                            </div>
                                        @endif
                                        
                                    </div>
                                </div>

                                {{-- 5. BOTTOM ACTIONS (Horizontally aligned and right justified) --}}
                                <div class="pt-3 border-t border-gray-100 flex justify-end gap-3 items-center">
                                    
                                    {{-- Actions based on Trashed Status --}}
                                    @if(!$isTrashed)
                                        <button type="button"
                                            class="px-4 py-2 text-xs font-medium rounded-lg bg-rose-600 text-white hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-600/20 transition"
                                            wire:click="softDelete({{ $b->vehiclebooking_id }})">
                                            Delete
                                        </button>
                                    @else
                                        <button type="button"
                                            class="px-4 py-2 text-xs font-medium rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-600/20 transition"
                                            wire:click="restore({{ $b->vehiclebooking_id }})">
                                            Restore
                                        </button>
                                    @endif
                                </div>
                            </div>
                            {{-- END: MODIFIED VEHICLE HISTORY CARD DESIGN --}}
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Pagination --}}
                @if(method_exists($bookings, 'links'))
                    <div class="px-4 sm:px-6 py-5 bg-gray-50 border-t border-gray-200 rounded-b-2xl">
                        <div class="flex justify-center">
                            {{ $bookings->links() }}
                        </div>
                    </div>
                @endif
            </section>

            {{-- SIDEBAR --}}
            <aside class="hidden md:flex md:flex-col md:col-span-1 gap-4">
                <section class="{{ $card }}">
                    <div class="px-4 py-4 border-b border-gray-200">
                        <h3 class="text-sm font-semibold text-gray-900">Filter by Vehicle</h3>
                        <p class="text-xs text-gray-500 mt-1">Klik kendaraan untuk filter.</p>
                    </div>

                    <div class="px-4 py-3 max-h-64 overflow-y-auto">
                        <button type="button" wire:click="$set('vehicleFilter', null)"
                            class="w-full flex items-center justify-between gap-2 px-3 py-2 rounded-lg text-xs font-medium {{ is_null($vehicleFilter) ? 'bg-gray-900 text-white' : 'text-gray-800 hover:bg-gray-100' }}">
                            <span class="flex items-center gap-2">
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-md border border-gray-300 text-[11px]">All</span>
                                <span>All Vehicles</span>
                            </span>
                            @if(is_null($vehicleFilter))
                                <span class="text-[10px] uppercase tracking-wide opacity-80">Active</span>
                            @endif
                        </button>

                        <div class="mt-2 space-y-1.5">
                            @forelse($vehicles as $v)
                                @php
                                    $vLabel = $v->name ?? $v->plate_number ?? '#'.$v->vehicle_id;
                                    $active = !is_null($vehicleFilter) && (int)$vehicleFilter === (int)$v->vehicle_id;
                                @endphp

                                <button type="button"
                                    wire:click="$set('vehicleFilter', {{ $v->vehicle_id }})"
                                    class="w-full flex items-center justify-between gap-2 px-3 py-2 rounded-lg text-xs {{ $active ? 'bg-gray-900 text-white' : 'text-gray-800 hover:bg-gray-100' }}">
                                    <span class="flex items-center gap-2">
                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-md border border-gray-300 text-[11px]">
                                            {{ substr($vLabel,0,2) }}
                                        </span>
                                        <span class="truncate">{{ $vLabel }}</span>
                                    </span>
                                    @if($active)
                                        <span class="text-[10px] uppercase tracking-wide opacity-80">Active</span>
                                    @endif
                                </button>
                            @empty
                                <p class="text-xs text-gray-500">Tidak ada kendaraan.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="px-4 pt-3 pb-4 border-t border-gray-200 bg-gray-50">
                        <h4 class="text-xs font-semibold text-gray-900 mb-2">Quick Stats</h4>
                        <div class="space-y-2 text-xs">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600">Total Vehicles</span>
                                <span class="font-semibold text-gray-900">{{ count($vehicles) }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600">
                                    {{ $statusTab === 'rejected' ? 'Rejected Records' : 'Completed Records' }}
                                </span>
                                <span class="font-semibold text-gray-900">{{ $bookings->total() }}</span>
                            </div>
                        </div>
                    </div>
                </section>
            </aside>
        </div>
    </main>
</div>