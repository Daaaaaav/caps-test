<div class="bg-gray-50 min-h-screen" wire:key="room-monitoring-history">
    @php
    // LAYOUT HELPERS (Harmonized)
    $card = 'bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden';
    $label = 'block text-sm font-medium text-gray-700 mb-2';
    $input = 'w-full h-10 px-3 rounded-lg border border-gray-300 text-gray-800 placeholder:text-gray-400 focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10 bg-white transition';

    // BUTTONS (Matching the requested style)
    $btnBlk = 'px-3 py-2 text-xs font-medium rounded-lg bg-gray-900 text-white hover:bg-black focus:outline-none focus:ring-2 focus:ring-gray-900/20 disabled:opacity-60 transition inline-flex items-center justify-center';
    $btnLt = 'px-3 py-2 text-xs font-medium rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-300/40 disabled:opacity-60 transition inline-flex items-center justify-center';
    // --- UPDATED: Changed to rose-600/700 for the delete button variable ---
    $btnDanger = 'px-3 py-2 text-xs font-medium rounded-lg bg-rose-600 text-white hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-600/40 disabled:opacity-60 transition inline-flex items-center justify-center'; // DELETE BUTTON STYLE
    // ---------------------------------------------------------------------

    // BADGES & ICONS (Harmonized)
    $chip = 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium ring-1 ring-inset';
    $chipInfo = 'bg-gray-100 text-gray-700 ring-gray-200';
    $chipStatus = [
    'APPROVED' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
    'PENDING' => 'bg-amber-50 text-amber-700 ring-amber-200',
    'REJECTED' => 'bg-rose-50 text-rose-700 ring-rose-200',
    'COMPLETED' => 'bg-blue-50 text-blue-700 ring-blue-200',
    'REQUEST' => 'bg-amber-50 text-amber-700 ring-amber-200',
    ];
    $mono = 'text-[10px] font-mono text-gray-500 bg-gray-100 px-2.5 py-1 rounded-md';
    $titleC = 'text-base font-semibold text-gray-900';
    $field = 'text-sm text-gray-600';
    $itemCard = 'bg-white border border-gray-200 rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow duration-150';
    $detailItem = 'py-3 border-b border-gray-100';
    @endphp

    <main class="px-4 sm:px-6 py-6 space-y-8">
        {{-- HEADER SECTION --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-gray-900 to-black text-white shadow-2xl">
            <div class="pointer-events-none absolute inset-0 opacity-10">
                <div class="absolute top-0 -right-6 w-28 h-28 bg-white/20 rounded-full blur-xl"></div>
                <div class="absolute bottom-0 -left-6 w-20 h-20 bg-white/10 rounded-full blur-lg"></div>
            </div>

            <div class="relative z-10 p-6 sm:p-8">
                <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                    {{-- LEFT SECTION --}}
                    <div class="flex items-start gap-4 sm:gap-6">
                        <div class="w-12 h-12 sm:w-14 sm:h-14 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm border border-white/20 shrink-0">
                            <x-heroicon-o-information-circle class="w-6 h-6 text-white" />
                        </div>

                        <div class="space-y-1.5">
                            <h2 class="text-xl sm:text-2xl font-semibold leading-tight">
                                Booking Room Center
                            </h2>

                            <div class="text-sm text-white/80 flex flex-col sm:block">
                                <span>Cabang: <span class="font-semibold">{{ $company_name }}</span></span>
                                <span class="hidden sm:inline mx-2">•</span>
                                <span>Departemen: <span class="font-semibold">{{ $department_name }}</span></span>
                            </div>

                            <p class="text-xs text-white/60 pt-1 sm:pt-0">
                                Menampilkan informasi dan notifikasi untuk departemen:
                                <span class="font-medium">{{ $department_name }}</span>.
                            </p>
                        </div>
                    </div>

                    {{-- RIGHT SECTION --}}
                    @if ($showSwitcher)
                    <div class="w-full lg:w-[32rem] lg:ml-6">
                        <label class="block text-xs font-medium text-white/80 mb-2">
                            Pilih Departemen
                        </label>
                        <select
                            wire:model.live="selected_department_id"
                            class="w-full h-11 sm:h-12 px-3 sm:px-4 rounded-lg border border-white/20 bg-white/10 text-white text-sm placeholder:text-white/60 focus:border-white focus:ring-2 focus:ring-white/30 focus:outline-none transition">
                            <option class="text-gray-900" value="{{ auth()->user()->department_id }}">
                                {{ auth()->user()->department->name }} (Your Primary Department)
                            </option>
                            @foreach ($deptOptions as $opt)
                            <option class="text-gray-900" value="{{ $opt['id'] }}">
                                {{ $opt['name'] }}{{ $opt['id'] === $primary_department_id ? ' — Primary' : '' }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    @else
                    {{-- SEARCH VERSION --}}
                    <div class="w-full lg:w-80 lg:ml-auto">
                        <label class="sr-only">Search</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 pl-3 flex items-center">
                                <x-heroicon-o-magnifying-glass class="w-4 h-4 text-gray-300" />
                            </span>
                            <input
                                type="text"
                                wire:model.live.debounce.400ms="search"
                                placeholder="Cari deskripsi atau catatan…"
                                class="w-full h-11 pl-9 pr-3 sm:pl-9 sm:pr-3.5 bg-white/10 border border-white/20 rounded-lg text-sm placeholder:text-gray-300 focus:outline-none focus:ring-2 focus:ring-white/30 focus:border-white transition">
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- FILTER & SEARCH SECTION --}}
        <div class="p-5 {{ $card }}">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <x-heroicon-o-funnel class="w-5 h-5 inline-block mr-1 align-text-bottom text-gray-500" />
                Filter Riwayat Booking
            </h3>

            <div class="flex flex-col sm:flex-row gap-4 items-end">
                {{-- STATUS FILTER --}}
                <div class="w-full sm:w-[20%]">
                    <label class="{{ $label }}">Filter Status</label>
                    <select
                        wire:model.live="statusFilter"
                        class="{{ $input }}">
                        <option value="">Semua Status</option>
                        <option value="APPROVED">Approved</option>
                        <option value="PENDING">Pending</option>
                        <option value="REJECTED">Rejected</option>
                        <option value="COMPLETED">Completed</option>
                        <option value="DELETED">Deleted</option>
                    </select>
                </div>

                {{-- SEARCH INPUT --}}
                <div class="w-full sm:w-[35%]">
                    <label class="{{ $label }}">Cari Deskripsi/Catatan</label>
                    <input
                        type="text"
                        wire:model.live.debounce.400ms="search"
                        placeholder="Cari judul atau catatan…"
                        class="{{ $input }}">
                </div>

                {{-- SORT SWITCHER BUTTON (Newest/Oldest) --}}
                <div class="w-full sm:w-[15%]">
                    <label class="{{ $label }}">Urutkan Data</label>
                    <button
                        type="button"
                        wire:click="toggleSortDirection"
                        class="w-full {{ $btnLt }} flex items-center justify-center">
                        @if ($sortDirection === 'desc')
                        <x-heroicon-o-arrow-down-circle class="w-4 h-4 mr-1" />
                        Terbaru
                        @else
                        <x-heroicon-o-arrow-up-circle class="w-4 h-4 mr-1" />
                        Terlama
                        @endif
                    </button>
                </div>

            </div>
        </div>


        {{-- GRID: OFFLINE (left) & ONLINE (right) --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- OFFLINE --}}
            <section>
                <div class="{{ $card }}">
                    <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between bg-gray-50">
                        <div class="flex items-center gap-2">
                            <x-heroicon-o-building-office-2 class="w-5 h-5 text-gray-700" />
                            <div>
                                <h3 class="{{ $titleC }}">Offline Meetings</h3>
                                <p class="text-xs text-gray-500">Riwayat meeting di ruang fisik.</p>
                            </div>
                        </div>
                        <span class="{{ $mono }}">Total: {{ $offline->total() }}</span>
                    </div>

                    {{-- LIST CONTAINER --}}
                    <div class="p-4 space-y-3 bg-gray-100">
                        @forelse ($offline as $b)
                        @php
                        // Check if soft-deleted
                        $isDeleted = !is_null($b->deleted_at);
                        $status = $isDeleted ? 'DELETED' : strtoupper($b->status ?? 'CANCELLED');
                        $statusChipClass = $isDeleted ? 'bg-rose-100 text-rose-800 ring-rose-300' : ($chipStatus[$status] ?? $chipInfo);
                        $rowNumber = $offline->firstItem() + $loop->index;
                        @endphp

                        <div class="{{ $itemCard }} {{ $isDeleted ? 'opacity-70 border-rose-300' : '' }}" wire:key="off-{{ $b->bookingroom_id }}">
                            <div class="flex items-start justify-between gap-3 border-b border-gray-100 pb-2 mb-2">
                                <div class="min-w-0">
                                    <div class=" text-gray-900 truncate {{ $isDeleted ? 'line-through text-gray-500' : '' }}">
                                        {{ $b->meeting_title }}
                                    </div>
                                    <div class="{{ $field }} mt-0.5 flex flex-wrap items-center gap-2">
                                        <span class="{{ $mono }}">#{{ $rowNumber }}</span>
                                        <span class="text-gray-400">•</span>
                                        <span>
                                            {{ \Illuminate\Support\Carbon::parse($b->start_time)->format('d M Y, H:i') }}
                                            – {{ \Illuminate\Support\Carbon::parse($b->end_time)->format('H:i') }}
                                        </span>
                                    </div>
                                </div>
                                <span class="{{ $chip }} {{ $statusChipClass }} shrink-0">
                                    <span class="font-medium">{{ $isDeleted ? 'Dihapus' : ucfirst(strtolower($status)) }}</span>
                                </span>
                            </div>

                            {{-- SUMMARY DETAILS --}}
                            <div class="space-y-1.5 text-sm text-gray-700">
                                <div class="flex items-center gap-2">
                                    <x-heroicon-o-rectangle-stack class="w-4 h-4 text-gray-400" />
                                    <span class="text-xs font-medium text-gray-500">Room:</span>
                                    <span class=" text-gray-800">{{ $b->room->room_name ?? '—' }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <x-heroicon-o-user-group class="w-4 h-4 text-gray-400" />
                                    <span class="text-xs font-medium text-gray-500">Attendees:</span>
                                    <span class=" text-gray-800">{{ $b->number_of_attendees }}</span>
                                </div>
                            </div>

                            {{-- ACTIONS BUTTONS --}}
                            <div class="mt-4 pt-3 border-t border-gray-100 flex gap-3">
                                <button
                                    type="button"
                                    wire:click="openDetailModal({{ $b->bookingroom_id }})"
                                    class="flex-1 inline-flex items-center justify-center rounded-lg border border-transparent bg-gray-900 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-2 transition">
                                    <x-heroicon-o-eye class="w-4 h-4 mr-1.5" />
                                    Lihat Detail
                                </button>

                                {{-- DELETE BUTTON (Conditionally rendered) --}}
                                @if (!$isDeleted)
                                <button
                                    type="button"
                                    wire:click="openDeleteConfirmModal({{ $b->bookingroom_id }})"
                                    class="flex-shrink-0 inline-flex items-center justify-center rounded-lg border border-transparent bg-rose-600 px-2.5 py-2 text-xs font-medium text-white shadow-sm hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-600 focus:ring-offset-2 transition">
                                    <x-heroicon-o-trash class="w-4 h-4" />
                                </button>
                                @endif
                            </div>
                        </div>
                        @empty
                        <div class="p-10 text-center text-gray-500 text-sm bg-white rounded-xl">
                            Tidak ada riwayat offline meeting.
                        </div>
                        @endforelse
                    </div>

                    {{-- Pagination Links for Offline --}}
                    @if ($offline->hasPages())
                    <div class="px-5 py-4 bg-gray-50 border-t border-gray-200">
                        <div class="flex justify-center">
                            {{ $offline->links(data: ['pageName' => 'offlinePage']) }}
                        </div>
                    </div>
                    @endif
                </div>
            </section>

            {{-- ONLINE --}}
            <section>
                <div class="{{ $card }}">
                    <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between bg-gray-50">
                        <div class="flex items-center gap-2">
                            <x-heroicon-o-wifi class="w-5 h-5 text-gray-700" />
                            <div>
                                <h3 class="{{ $titleC }}">Online Meetings</h3>
                                <p class="text-xs text-gray-500">Riwayat meeting via platform online.</p>
                            </div>
                        </div>
                        <span class="{{ $mono }}">Total: {{ $online->total() }}</span>
                    </div>

                    {{-- LIST CONTAINER --}}
                    <div class="p-4 space-y-3 bg-gray-100">
                        @forelse ($online as $b)
                        @php
                        // Check if soft-deleted
                        $isDeleted = !is_null($b->deleted_at);
                        $status = $isDeleted ? 'DELETED' : strtoupper($b->status ?? 'CANCELLED');
                        $statusChipClass = $isDeleted ? 'bg-rose-100 text-rose-800 ring-rose-300' : ($chipStatus[$status] ?? $chipInfo);
                        $rowNumber = $online->firstItem() + $loop->index;
                        @endphp

                        <div class="{{ $itemCard }} {{ $isDeleted ? 'opacity-70 border-rose-300' : '' }}" wire:key="on-{{ $b->bookingroom_id }}">
                            <div class="flex items-start justify-between gap-3 border-b border-gray-100 pb-2 mb-2">
                                <div class="min-w-0">
                                    <div class=" text-gray-900 truncate {{ $isDeleted ? 'line-through text-gray-500' : '' }}">
                                        {{ $b->meeting_title }}
                                    </div>
                                    <div class="{{ $field }} mt-0.5 flex flex-wrap items-center gap-2">
                                        <span class="{{ $mono }}">#{{ $rowNumber }}</span>
                                        <span class="text-gray-400">•</span>
                                        <span>
                                            {{ \Illuminate\Support\Carbon::parse($b->start_time)->format('d M Y, H:i') }}
                                            – {{ \Illuminate\Support\Carbon::parse($b->end_time)->format('H:i') }}
                                        </span>
                                    </div>
                                </div>
                                <span class="{{ $chip }} {{ $statusChipClass }} shrink-0">
                                    <span class="font-medium">{{ $isDeleted ? 'Dihapus' : ucfirst(strtolower($status)) }}</span>
                                </span>
                            </div>

                            {{-- SUMMARY DETAILS --}}
                            <div class="space-y-1.5 text-sm text-gray-700">
                                <div class="flex items-center gap-2">
                                    <x-heroicon-o-swatch class="w-4 h-4 text-gray-400" />
                                    <span class="text-xs font-medium text-gray-500">Provider:</span>
                                    <span class=" text-gray-800 capitalize">{{ str_replace('_', ' ', $b->online_provider ?? '—') }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <x-heroicon-o-user-group class="w-4 h-4 text-gray-400" />
                                    <span class="text-xs font-medium text-gray-500">Attendees:</span>
                                    <span class=" text-gray-800">{{ $b->number_of_attendees }}</span>
                                </div>
                            </div>

                            {{-- ACTIONS BUTTONS --}}
                            <div class="mt-4 pt-3 border-t border-gray-100 flex gap-3">
                                <button
                                    type="button"
                                    wire:click="openDetailModal({{ $b->bookingroom_id }})"
                                    class="flex-1 inline-flex items-center justify-center rounded-lg border border-transparent bg-gray-900 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-2 transition">
                                    <x-heroicon-o-eye class="w-4 h-4 mr-1.5" />
                                    Lihat Detail
                                </button>

                                {{-- DELETE BUTTON (Conditionally rendered) --}}
                                @if (!$isDeleted)
                                <button
                                    type="button"
                                    wire:click="openDeleteConfirmModal({{ $b->bookingroom_id }})"
                                    class="flex-shrink-0 inline-flex items-center justify-center rounded-lg border border-transparent bg-rose-600 px-2.5 py-2 text-xs font-medium text-white shadow-sm hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-600 focus:ring-offset-2 transition">
                                    <x-heroicon-o-trash class="w-4 h-4" />
                                </button>
                                @endif
                            </div>
                        </div>
                        @empty
                        <div class="p-10 text-center text-gray-500 text-sm bg-white rounded-xl">
                            Tidak ada riwayat online meeting.
                        </div>
                        @endforelse
                    </div>

                    {{-- Pagination Links for Online --}}
                    @if ($online->hasPages())
                    <div class="px-5 py-4 bg-gray-50 border-t border-gray-200">
                        <div class="flex justify-center">
                            {{ $online->links(data: ['pageName' => 'onlinePage']) }}
                        </div>
                    </div>
                    @endif
                </div>
            </section>
        </div>
    </main>

    {{-- BOOKING DETAIL MODAL (With Requirement Details) --}}
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

                {{-- Title and Status --}}
                <div class="pb-2 border-b border-gray-100">
                    <h4 class="text-lg font-bold text-gray-900 mb-1">{{ $selectedBookingDetail->meeting_title }}</h4>
                    <span class="{{ $chip }} {{ !is_null($selectedBookingDetail->deleted_at) ? 'bg-rose-100 text-rose-800 ring-rose-300' : ($chipStatus[strtoupper($selectedBookingDetail->status ?? 'CANCELLED')] ?? $chipInfo) }}">
                        Status: {{ !is_null($selectedBookingDetail->deleted_at) ? 'Dihapus' : ucfirst(strtolower($selectedBookingDetail->status ?? 'Cancelled')) }}
                    </span>
                    <span class="{{ $mono }} ml-2">ID: {{ $selectedBookingDetail->bookingroom_id }}</span>
                </div>

                <div class="divide-y divide-gray-100">

                    {{-- REQUIREMENT DETAILS (FIXED: Using inline badges/chips) --}}
                    @if ($selectedBookingDetail->requirements->isNotEmpty())
                    <div class="{{ $detailItem }}">
                        <div class="text-xs font-medium text-gray-500 flex items-center gap-1.5 mb-2 pb-1 border-b border-gray-100">
                            <x-heroicon-o-check-badge class="w-4 h-4 text-gray-400" />
                            Daftar Kebutuhan:
                        </div>

                        <div class="flex flex-wrap gap-2 mt-2">
                            @foreach ($selectedBookingDetail->requirements as $requirement)
                            <span class="{{ $chip }} bg-gray-100 text-gray-700 ring-gray-300">
                                {{ $requirement->name ?? 'N/A' }}
                            </span>
                            @endforeach
                        </div>
                    </div>
                    @endif


                    {{-- Date & Time --}}
                    <div class="{{ $detailItem }}">
                        <div class="text-xs font-medium text-gray-500 flex items-center gap-1.5 mb-1">
                            <x-heroicon-o-calendar class="w-4 h-4 text-gray-400" />
                            Waktu Booking
                        </div>
                        <p class="text-sm font-semibold text-gray-800">
                            {{ \Illuminate\Support\Carbon::parse($selectedBookingDetail->start_time)->format('d M Y') }}
                            <span class="text-gray-400 mx-1">/</span>
                            {{ \Illuminate\Support\Carbon::parse($selectedBookingDetail->start_time)->format('H:i') }} – {{ \Illuminate\Support\Carbon::parse($selectedBookingDetail->end_time)->format('H:i') }}
                        </p>
                    </div>

                    {{-- Type Details --}}
                    <div class="{{ $detailItem }} grid grid-cols-2 gap-4">
                        <div>
                            <div class="text-xs font-medium text-gray-500 flex items-center gap-1.5 mb-1">
                                <x-heroicon-o-user-group class="w-4 h-4 text-gray-400" /> Jumlah Peserta
                            </div>
                            <p class="text-sm font-semibold text-gray-800">{{ $selectedBookingDetail->number_of_attendees }}</p>
                        </div>
                        @if ($selectedBookingDetail->booking_type === 'meeting')
                        <div>
                            <div class="text-xs font-medium text-gray-500 flex items-center gap-1.5 mb-1">
                                <x-heroicon-o-building-office-2 class="w-4 h-4 text-gray-400" /> Ruang Meeting
                            </div>
                            <p class="text-sm font-semibold text-gray-800">{{ $selectedBookingDetail->room->room_name ?? '—' }}</p>
                        </div>
                        @else
                        <div>
                            <div class="text-xs font-medium text-gray-500 flex items-center gap-1.5 mb-1">
                                <x-heroicon-o-swatch class="w-4 h-4 text-gray-400" /> Provider Online
                            </div>
                            <p class="text-sm font-semibold text-gray-800 capitalize">{{ str_replace('_', ' ', $selectedBookingDetail->online_provider ?? '—') }}</p>
                        </div>
                        @endif
                    </div>

                    {{-- Online Specific Details --}}
                    @if ($selectedBookingDetail->booking_type === 'online_meeting')
                    <div class="{{ $detailItem }} grid grid-cols-2 gap-4">
                        <div>
                            <div class="text-xs font-medium text-gray-500 mb-1">Kode Meeting</div>
                            <p class="text-sm font-semibold text-gray-800">{{ $selectedBookingDetail->online_meeting_code ?: '—' }}</p>
                        </div>
                        <div>
                            <div class="text-xs font-medium text-gray-500 mb-1">Password</div>
                            <p class="text-sm font-semibold text-gray-800">{{ $selectedBookingDetail->online_meeting_password ?: '—' }}</p>
                        </div>
                    </div>

                    <div class="{{ $detailItem }}">
                        <div class="text-xs font-medium text-gray-500 mb-1 flex items-center gap-1.5">
                            <x-heroicon-o-link class="w-4 h-4 text-gray-400" /> Meeting URL
                        </div>
                        @if ($selectedBookingDetail->online_meeting_url)
                        <a href="{{ $selectedBookingDetail->online_meeting_url }}" target="_blank"
                            class="text-blue-600 hover:underline break-all text-sm">
                            {{ $selectedBookingDetail->online_meeting_url }}
                        </a>
                        @else
                        <p class="text-sm text-gray-700">—</p>
                        @endif
                    </div>
                    @endif

                    {{-- Notes --}}
                    <div class="pt-3">
                        <div class="text-xs font-medium text-gray-500 mb-1 flex items-center gap-1.5">
                            <x-heroicon-o-document-text class="w-4 h-4 text-gray-400" /> Catatan Khusus Booking
                        </div>
                        <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $selectedBookingDetail->special_notes ?: '—' }}</p>
                    </div>

                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="bg-gray-50 px-5 py-4 flex justify-end">
                <button wire:click="closeDetailModal" type="button"
                    class="{{ $btnLt }} inline-flex items-center gap-1.5">
                    <x-heroicon-o-x-mark class="w-4 h-4" />
                    <span>Tutup</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- DELETE CONFIRMATION MODAL --}}
    {{-- DELETE CONFIRMATION MODAL (Updated to match Detail Modal Style) --}}
    @if ($showDeleteConfirmModal && $bookingToDeleteId)
    <div
        class="fixed inset-0 z-[70] flex items-center justify-center"
        role="dialog" aria-modal="true"
        wire:key="delete-modal-{{ $bookingToDeleteId }}"
        wire:keydown.escape.window="closeDeleteConfirmModal">
        <button type="button" class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-opacity" aria-label="Close overlay" wire:click="closeDeleteConfirmModal"></button>

        {{-- CONTAINER MATCHING DETAIL MODAL --}}
        <div class="relative w-full max-w-lg mx-4 bg-white rounded-2xl border border-gray-200 shadow-xl overflow-hidden focus:outline-none transform transition-all" tabindex="-1">

            {{-- Modal Header --}}
            <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-trash class="w-5 h-5 text-red-600" />
                    <h3 class="text-base font-semibold text-gray-900">Konfirmasi Penghapusan Booking</h3>
                </div>
                <button class="text-gray-500 hover:text-gray-700" type="button" wire:click="closeDeleteConfirmModal" aria-label="Close">
                    <x-heroicon-o-x-mark class="w-5 h-5" />
                </button>
            </div>

            {{-- Modal Body --}}
            <div class="p-5 text-center">
                <x-heroicon-o-exclamation-triangle class="w-16 h-16 mx-auto text-red-500 mb-4" /> {{-- Larger icon for focus --}}

                <h4 class="mt-2 text-lg font-bold text-gray-900">Konfirmasi Penghapusan</h4>

                <p class="mt-2 text-sm text-gray-700">
                    Anda yakin ingin menghapus (soft-delete) Booking ID: <span class="font-bold text-red-600">{{ $bookingToDeleteId }}</span>?
                </p>
                <p class="mt-1 text-xs text-gray-500">
                    Tindakan ini akan membuat data tidak aktif. Anda dapat mengaktifkan kembali data yang dihapus dari database (jika ada fitur restore).
                </p>
            </div>

            {{-- Modal Footer MATCHING DETAIL MODAL --}}
            <div class="bg-gray-50 px-5 py-4 flex justify-end gap-3">
                <button wire:click="closeDeleteConfirmModal" type="button" class="{{ $btnLt }} inline-flex items-center gap-1.5">
                    <x-heroicon-o-x-mark class="w-4 h-4" />
                    <span>Batal</span>
                </button>
                <button wire:click.prevent="softDeleteBooking" type="button" class="inline-flex items-center justify-center rounded-lg border border-transparent bg-rose-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-600 focus:ring-offset-2 transition">
                    <x-heroicon-o-trash class="w-4 h-4 mr-1.5" />
                    Ya, Hapus Sekarang
                </button>
            </div>
        </div>
    </div>
    @endif
</div>