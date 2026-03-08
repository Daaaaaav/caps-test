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
            <div class="relative z-10 p-6 sm:p-8">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm border border-white/20">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg sm:text-xl font-semibold">Vehicle Status</h2>
                            <p class="text-sm text-white/80">Kelola peminjaman: Pending / Approved / On Progress / Returned.</p>
                        </div>
                    </div>

                    <label class="inline-flex items-center gap-2 text-sm text-white/90 cursor-pointer">
                        <input type="checkbox" wire:model.live="includeDeleted"
                               class="w-4 h-4 rounded border-white/30 bg-white/10 text-gray-900 focus:ring-2 focus:ring-white/20 cursor-pointer">
                        <span>Include Deleted</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            {{-- LIST --}}
            <section class="{{ $card }} md:col-span-3">
                <div class="px-4 sm:px-6 pt-4 pb-3 border-b border-gray-200 space-y-3">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900">Vehicle Bookings</h3>
                            <p class="text-xs text-gray-500">Daftar peminjaman kendaraan.</p>
                        </div>

                        {{-- Tabs --}}
                        <div class="inline-flex items-center bg-gray-100 rounded-full p-1 text-xs font-medium">
                            @foreach(['pending'=>'Pending','approved'=>'Approved','on_progress'=>'On Progress','returned'=>'Returned'] as $key=>$lbl)
                                <button type="button"
                                        wire:click="$set('statusTab','{{ $key }}')"
                                        class="px-3 py-1 rounded-full transition {{ $statusTab === $key ? 'bg-gray-900 text-white shadow-sm' : 'text-gray-700 hover:bg-gray-200' }}">
                                    {{ $lbl }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Filters --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="{{ $label }}">Search</label>
                            <div class="relative">
                                <input type="text" class="{{ $input }} pl-9" placeholder="Search purpose, destination, borrower…"
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

                {{-- LIST BODY – 2-column bento cards --}}
                @if($bookings->isEmpty())
                    <div class="px-4 sm:px-6 py-14 text-center text-gray-500 text-sm">
                        Tidak ada data pada filter ini.
                    </div>
                @else
                    <div class="px-4 sm:px-6 py-5">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            @forelse($bookings as $b)
                                @php
                                    $vehicleName = $vehicleMap[$b->vehicle_id] ?? 'Unknown';
                                    $avatarChar  = strtoupper(substr($vehicleName, 0, 1));
                                    $beforeC = $photoCounts[$b->vehiclebooking_id]['before'] ?? 0;
                                    $afterC  = $photoCounts[$b->vehiclebooking_id]['after']  ?? 0;
                                    $statusColors = [
                                        'pending'      => ['bg'=>'bg-amber-100','text'=>'text-amber-800','label'=>'Pending'],
                                        'approved'     => ['bg'=>'bg-emerald-100','text'=>'text-emerald-800','label'=>'Approved'],
                                        'on_progress'  => ['bg'=>'bg-blue-100','text'=>'text-blue-800','label'=>'On Progress'],
                                        'returned'     => ['bg'=>'bg-indigo-100','text'=>'text-indigo-800','label'=>'Returned'],
                                        'rejected'     => ['bg'=>'bg-rose-100','text'=>'text-rose-800','label'=>'Rejected'],
                                        'completed'    => ['bg'=>'bg-emerald-100','text'=>'text-emerald-800','label'=>'Completed'],
                                    ];
                                    $statusStyle = $statusColors[$b->status] ?? ['bg'=>'bg-gray-100','text'=>'text-gray-800','label'=>ucfirst($b->status)];
                                @endphp

                                {{-- START: MODIFIED VEHICLE BOOKING CARD DESIGN --}}
                                <div wire:key="booking-{{ $b->vehiclebooking_id }}"
                                     class="bg-white border border-gray-200 rounded-xl p-4 space-y-3 hover:shadow-sm hover:border-gray-300 transition">
                                    
                                    <div class="flex items-start gap-4">
                                        {{-- 1. Avatar/Initial on the left --}}
                                        <div class="{{ $icoAvatar }} mt-0.5">{{ $avatarChar }}</div>
                                        
                                        <div class="flex-1 min-w-0">
                                            {{-- 2. TOP ROW: Title, Status, ID --}}
                                            <div class="flex items-center justify-between gap-3 min-w-0 mb-2">
                                                <h4 class="font-semibold text-gray-900 text-base truncate pr-2 cursor-pointer"
                                                    wire:click="showDetails({{ $b->vehiclebooking_id }})">
                                                    {{ $b->purpose ? ucfirst($b->purpose) : 'Vehicle Booking' }}
                                                </h4>
                                                <div class="flex-shrink-0 flex items-center gap-2">
                                                    {{-- Status Badge --}}
                                                    <span class="text-[11px] px-2 py-0.5 rounded-full flex-shrink-0 {{ $statusStyle['bg'] }} {{ $statusStyle['text'] }}">
                                                        {{ $statusStyle['label'] }}
                                                    </span>
                                                    {{-- ID Chip --}}
                                                    <span class="text-[11px] px-2 py-0.5 rounded-full bg-gray-50 text-gray-700 border border-gray-200 flex-shrink-0">
                                                        #{{ $b->vehiclebooking_id }}
                                                    </span>
                                                </div>
                                            </div>

                                            {{-- 3. MIDDLE SECTION: Vehicle, Date, Time, Borrower --}}
                                            <div class="space-y-2 text-[13px] text-gray-600 mb-3 border-y border-gray-100 py-2">
                                                
                                                {{-- Vehicle and Borrower --}}
                                                <div class="flex flex-wrap items-center gap-4">
                                                    <span class="{{ $chip }} text-xs px-2.5 py-0.5 bg-gray-100">
                                                        <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                  d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" />
                                                        </svg>
                                                        <span class="font-medium text-gray-700">{{ $vehicleName }}</span>
                                                    </span>
                                                    
                                                    @if(!empty($b->borrower_name))
                                                        <span class="{{ $chip }} text-xs px-2.5 py-0.5 bg-gray-100">
                                                            <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                            </svg>
                                                            <span class="font-medium text-gray-700">{{ $b->borrower_name }}</span>
                                                        </span>
                                                    @endif
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

                                            {{-- 4. BOTTOM LEFT: Photo Counts & Created Timestamp --}}
                                            <div class="text-[12px] text-gray-600 space-y-1">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-gray-50 border border-gray-200 text-[11px]">
                                                        Foto Before: <span class="font-medium text-gray-800">{{ $beforeC }}</span>
                                                    </span>
                                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-gray-50 border border-gray-200 text-[11px]">
                                                        Foto After: <span class="font-medium text-gray-800">{{ $afterC }}</span>
                                                    </span>
                                                </div>
                                                <span class="inline-block text-[10px] text-gray-500 mt-1">
                                                    Created: {{ optional($b->created_at)->timezone('Asia/Jakarta')->format('d M Y H:i') }}
                                                </span>
                                            </div>

                                            {{-- Rejected Note --}}
                                            @if($b->reject_note && $b->status === 'rejected')
                                                <div class="mt-2 text-xs text-rose-700 bg-rose-50 border border-rose-100 rounded-lg p-2">
                                                    <span class="font-medium">Rejected Reason:</span> {{ $b->reject_note }}
                                                </div>
                                            @endif
                                            
                                        </div>
                                    </div>

                                    {{-- 5. BOTTOM ACTIONS (Horizontally aligned and right justified) --}}
                                    <div class="pt-3 border-t border-gray-100 flex justify-end gap-3 items-center">
                                        
                                        {{-- Actions based on Status --}}
                                        @if($b->status === 'pending')
                                            {{-- Reject Button (Ghost Style) --}}
                                            <button type="button"
                                                    wire:click.stop="confirmReject({{ $b->vehiclebooking_id }})"
                                                    wire:loading.attr="disabled"
                                                    wire:target="confirmReject({{ $b->vehiclebooking_id }})"
                                                    class="px-4 py-2 text-xs font-medium rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-300/20 disabled:opacity-60 transition">
                                                Reject
                                            </button>

                                            {{-- Approve Button (Primary Style) --}}
                                            <button type="button"
                                                    wire:click.stop="approve({{ $b->vehiclebooking_id }})"
                                                    wire:loading.attr="disabled"
                                                    wire:target="approve({{ $b->vehiclebooking_id }})"
                                                    class="px-4 py-2 text-xs font-medium rounded-lg bg-gray-900 text-white hover:bg-black focus:outline-none focus:ring-2 focus:ring-gray-900/20 disabled:opacity-60 transition">
                                                Approve
                                            </button>
                                        @elseif($b->status === 'on_progress')
                                            {{-- Mark Returned Button --}}
                                            <button type="button"
                                                    wire:click.stop="markReturned({{ $b->vehiclebooking_id }})"
                                                    wire:loading.attr="disabled"
                                                    wire:target="markReturned({{ $b->vehiclebooking_id }})"
                                                    class="px-4 py-2 text-xs font-medium rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-600/20 disabled:opacity-60 transition">
                                                Mark Returned
                                            </button>
                                        @elseif($b->status === 'returned')
                                            {{-- Mark Done Button (Conditional styling based on after photos) --}}
                                            <button type="button"
                                                    wire:click.stop="markDone({{ $b->vehiclebooking_id }})"
                                                    wire:loading.attr="disabled"
                                                    wire:target="markDone({{ $b->vehiclebooking_id }})"
                                                    class="px-4 py-2 text-xs font-medium rounded-lg {{ $afterC === 0 ? 'bg-gray-200 text-gray-500 cursor-not-allowed' : 'bg-indigo-600 text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-600/20' }}"
                                                    @disabled($afterC === 0)>
                                                Mark Done
                                            </button>
                                            @if($afterC === 0)
                                                <span class="text-[11px] text-gray-500 block">
                                                    Waiting for after photos
                                                </span>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                                {{-- END: MODIFIED VEHICLE BOOKING CARD DESIGN --}}
                            @empty
                                <div class="col-span-full text-center text-gray-500 text-sm py-6">
                                    Tidak ada data pada filter ini.
                                </div>
                            @endforelse
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

            {{-- SIDEBAR: vehicle filter --}}
            <aside class="hidden md:flex md:flex-col md:col-span-1 gap-4">
                <section class="{{ $card }}">
                    <div class="px-4 py-4 border-b border-gray-200">
                        <h3 class="text-sm font-semibold text-gray-900">Filter by Vehicle</h3>
                        <p class="text-xs text-gray-500 mt-1">Klik kendaraan untuk mem-filter.</p>
                    </div>

                    <div class="px-4 py-3 max-h-64 overflow-y-auto">
                        <button type="button"
                                wire:click="$set('vehicleFilter', null)"
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
                                    $vLabel = $v->name ?? $v->plate_number ?? ('#'.$v->vehicle_id);
                                    $active = !is_null($vehicleFilter) && (int)$vehicleFilter === (int)$v->vehicle_id;
                                @endphp
                                <button type="button"
                                        wire:click="$set('vehicleFilter', {{ $v->vehicle_id }})"
                                        class="w-full flex items-center justify-between gap-2 px-3 py-2 rounded-lg text-xs {{ $active ? 'bg-gray-900 text-white' : 'text-gray-800 hover:bg-gray-100' }}">
                                    <span class="flex items-center gap-2">
                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-md border border-gray-300 text-[11px]">
                                            {{ substr($vLabel, 0, 2) }}
                                        </span>
                                        <span class="truncate">{{ $vLabel }}</span>
                                    </span>
                                    @if($active)
                                        <span class="text-[10px] uppercase tracking-wide opacity-80">Active</span>
                                    @endif
                                </button>
                            @empty
                                <p class="text-xs text-gray-500">Tidak ada data kendaraan.</p>
                            @endforelse
                        </div>
                    </div>
                </section>
            </aside>
        </div>
    </main>

    {{-- DETAIL MODAL --}}
    @if($showDetailModal && $selectedBooking)
        <div x-data="{ show: @entangle('showDetailModal') }"
             x-show="show"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center p-4"
             style="display: none;">

            {{-- Backdrop --}}
            <div class="fixed inset-0 bg-gray-900/70 backdrop-blur-sm" wire:click="closeDetailModal"></div>

            {{-- Modal Content --}}
            <div x-show="show"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="relative z-10 w-full max-w-3xl bg-white rounded-2xl shadow-xl overflow-hidden">

                {{-- Header --}}
                <div class="flex items-start justify-between p-4 border-b border-gray-200">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">
                            Detail Booking #{{ $selectedBooking->vehiclebooking_id }}
                        </h3>
                        <p class="text-sm text-gray-600">
                            {{ $selectedBooking->purpose }}
                        </p>
                    </div>
                    <button type="button" wire:click="closeDetailModal" class="p-1 text-gray-400 hover:text-gray-700 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Body --}}
                <div class="p-6 space-y-6 max-h-[70vh] overflow-y-auto">
                    {{-- Detail Grid --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="block text-xs text-gray-500">Peminjam</span>
                            <span class="font-medium text-gray-800">{{ $selectedBooking->borrower_name }}</span>
                        </div>
                        <div>
                            <span class="block text-xs text-gray-500">Kendaraan</span>
                            <span class="font-medium text-gray-800">{{ $vehicleMap[$selectedBooking->vehicle_id] ?? 'N/A' }}</span>
                        </div>
                        <div>
                            <span class="block text-xs text-gray-500">Tujuan</span>
                            <span class="font-medium text-gray-800">{{ $selectedBooking->destination ?? 'N/A' }}</span>
                        </div>
                        <div>
                            <span class="block text-xs text-gray-500">Tipe Keperluan</span>
                            <span class="font-medium text-gray-800">{{ ucfirst($selectedBooking->purpose_type) }}</span>
                        </div>
                        <div>
                            <span class="block text-xs text-gray-500">Mulai</span>
                            <span class="font-medium text-gray-800">{{ fmtDate($selectedBooking->start_at) }}, {{ fmtTime($selectedBooking->start_at) }}</span>
                        </div>
                        <div>
                            <span class="block text-xs text-gray-500">Selesai</span>
                            <span class="font-medium text-gray-800">{{ fmtDate($selectedBooking->end_at) }}, {{ fmtTime($selectedBooking->end_at) }}</span>
                        </div>
                    </div>

                    <hr class="border-gray-200" />

                    {{-- Foto Sebelum --}}
                    <div>
                        <h4 class="text-base font-semibold text-gray-800 mb-3">Foto Sebelum Peminjaman</h4>
                        @forelse($selectedPhotos['before'] as $photo)
                            <div class="mb-4">
                                <a href="{{ asset('storage/' . $photo->photo_path) }}" target="_blank" class="block rounded-lg overflow-hidden border border-gray-200">
                                    <img src="{{ asset('storage/' . $photo->photo_path) }}" alt="Foto Before" class="w-full h-auto object-cover">
                                </a>
                                <span class="text-xs text-gray-500 mt-1 block">
                                    Di-upload oleh: {{ $photo->user->full_name ?? 'N/A' }}
                                </span>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">Tidak ada foto 'before' yang di-upload.</p>
                        @endforelse
                    </div>

                    <hr class="border-gray-200" />

                    {{-- Foto Sesudah --}}
                    <div>
                        <h4 class="text-base font-semibold text-gray-800 mb-3">Foto Setelah Peminjaman</h4>
                        @forelse($selectedPhotos['after'] as $photo)
                            <div class="mb-4">
                                <a href="{{ asset('storage/' . $photo->photo_path) }}" target="_blank" class="block rounded-lg overflow-hidden border border-gray-200">
                                    <img src="{{ asset('storage/' . $photo->photo_path) }}" alt="Foto After" class="w-full h-auto object-cover">
                                </a>
                                <span class="text-xs text-gray-500 mt-1 block">
                                    Di-upload oleh: {{ $photo->user->full_name ?? 'N/A' }}
                                </span>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">Tidak ada foto 'after' yang di-upload.</p>
                        @endforelse
                    </div>
                </div>

                {{-- Footer --}}
                <div class="px-4 py-3 bg-gray-50 border-t border-gray-200 text-right">
                    <button type="button"
                            wire:click="closeDetailModal"
                            class="px-3 py-2 text-xs font-medium rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-300/20 transition">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif
    
    {{-- REJECT MODAL --}}
    @if($showRejectModal && $rejectId)
        <div x-data="{ show: @entangle('showRejectModal') }"
             x-show="show"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center p-4"
             style="display: none;">

            {{-- Backdrop --}}
            <div class="fixed inset-0 bg-gray-900/70 backdrop-blur-sm" wire:click="cancelReject"></div>

            {{-- Modal Content --}}
            <div x-show="show"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="relative z-10 w-full max-w-lg bg-white rounded-2xl shadow-xl overflow-hidden">
                 
                <form wire:submit.prevent="submitReject">
                    {{-- Header --}}
                    <div class="flex items-start justify-between p-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Tolak Booking #{{ $rejectId }}
                        </h3>
                        <button type="button" wire:click="cancelReject" class="p-1 text-gray-400 hover:text-gray-700 transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    {{-- Body --}}
                    <div class="p-6 space-y-4">
                        <p class="text-sm text-gray-600">
                            Masukkan alasan penolakan untuk booking kendaraan ini. Alasan ini akan dicatat.
                        </p>
                        
                        <div>
                            <label for="reject-note" class="{{ $label }}">Alasan Penolakan <span class="text-red-500">*</span></label>
                            <textarea id="reject-note"
                                      wire:model.defer="rejectNote"
                                      rows="4"
                                      placeholder="Contoh: Kendaraan sedang perbaikan, atau tanggal bentrok."
                                      class="{{ $input }} !h-auto @error('rejectNote') border-red-500 @enderror"></textarea>
                            @error('rejectNote')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="px-4 py-3 bg-gray-50 border-t border-gray-200 text-right space-x-2">
                        <button type="button"
                                wire:click="cancelReject"
                                class="px-3 py-2 text-xs font-medium rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-300/20 transition">
                            Batal
                        </button>
                        <button type="submit"
                                wire:loading.attr="disabled"
                                class="px-3 py-2 text-xs font-medium rounded-lg bg-rose-600 text-white hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-600/20 disabled:opacity-60 transition">
                            Tolak Booking
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>