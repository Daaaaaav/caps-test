<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    {{-- Header --}}
    <div class="bg-white rounded-xl shadow-sm border-2 border-black p-4 md:p-6 mb-4 md:mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <h1 class="text-xl md:text-2xl font-bold text-gray-900">Vehicle Booking Status</h1>

            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 self-start md:self-center">
                {{-- Page Navigation --}}
                <div class="inline-flex rounded-lg overflow-hidden bg-gray-100 border border-gray-200">
                    <a href="{{ route('book-vehicle') }}"
                       class="px-3 md:px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50 transition-colors border-r border-gray-200 inline-flex items-center gap-2">
                        Book Vehicle
                    </a>
                    <a href="{{ route('vehiclestatus') }}"
                       class="px-3 md:px-4 py-2 text-sm font-medium bg-gray-900 text-white transition-colors inline-flex items-center gap-2">
                        Vehicle Status
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="bg-white rounded-xl shadow-sm border-2 border-black p-4 md:p-5">
        {{-- Search / Filters header --}}
        <div class="flex flex-col md:flex-row md:items-center gap-4 pb-4 mb-4 border-b border-gray-100">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-3 w-full">
                {{-- Search --}}
                <div class="relative md:col-span-1">
                    <input type="text"
                           wire:model.live.debounce.400ms="q"
                           placeholder="Search purpose / vehicle..."
                           class="w-full px-3 py-2 pl-9 text-sm text-gray-900 placeholder:text-gray-400 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent">
                    <x-heroicon-o-magnifying-glass class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"/>
                </div>

                {{-- Vehicle Filter --}}
                <div>
                    <select wire:model.live="vehicleFilter"
                            class="w-full px-3 py-2 text-sm text-gray-900 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent">
                        <option value="">All Vehicles</option>
                        @foreach($vehicles as $v)
                            @php $label = $v->name ?? $v->plate_number ?? ('#'.$v->vehicle_id); @endphp
                            <option value="{{ $v->vehicle_id }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Sort --}}
                <div>
                    <select wire:model.live="sortFilter"
                            class="w-full px-3 py-2 text-sm text-gray-900 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent">
                        <option value="recent">Newest first</option>
                        <option value="oldest">Oldest first</option>
                        <option value="nearest">Nearest time</option>
                    </select>
                </div>

                {{-- Status --}}
                <div>
                    <select wire:model.live="dbStatusFilter"
                            class="w-full px-3 py-2 text-sm text-gray-900 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent">
                        <option value="all">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="on_progress">On Progress</option>
                        <option value="returned">Returned</option>
                        <option value="rejected">Rejected</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- List --}}
        <div class="space-y-4">
            @forelse($bookings as $b)
                @php
                    $start = \Carbon\Carbon::parse($b->start_at, 'Asia/Jakarta');
                    $end = \Carbon\Carbon::parse($b->end_at, 'Asia/Jakarta');
                    $dateStr = $start->format('D, M j, Y');
                    $timeStr = $start->format('H:i').'–'.$end->format('H:i');
                    $vehicleName = $vehicleMap[$b->vehicle_id] ?? 'Unknown';

                    // Status Configuration
                    $statusConfig = match($b->status) {
                        'pending'     => ['class' => 'bg-yellow-100 text-yellow-800 border-yellow-200', 'icon' => 'clock'],
                        'approved'    => ['class' => 'bg-green-100 text-green-800 border-green-200', 'icon' => 'check-circle'],
                        'on_progress' => ['class' => 'bg-blue-100 text-blue-800 border-blue-200', 'icon' => 'play-circle'],
                        'returned'    => ['class' => 'bg-indigo-100 text-indigo-800 border-indigo-200', 'icon' => 'arrow-path'],
                        'rejected'    => ['class' => 'bg-red-100 text-red-800 border-red-200', 'icon' => 'x-circle'],
                        'cancelled'   => ['class' => 'bg-gray-100 text-gray-600 border-gray-200', 'icon' => 'no-symbol'],
                        'completed'   => ['class' => 'bg-gray-100 text-gray-800 border-gray-200', 'icon' => 'archive-box'],
                        default       => ['class' => 'bg-gray-50 text-gray-600 border-gray-200', 'icon' => 'question-mark-circle'],
                    };

                    // Photo Counts
                    $currentPhotoCounts = $photoCounts[$b->vehiclebooking_id] ?? [];
                    $beforeC = $currentPhotoCounts['before'] ?? 0;
                    $afterC  = $currentPhotoCounts['after'] ?? 0;

                    // Clickable Logic
                    $isClickable = in_array($b->status, ['approved', 'returned']);
                    $cardTag = $isClickable ? 'a' : 'div';
                    $cardLink = $isClickable ? route('book-vehicle', ['id' => $b->vehiclebooking_id]) : null;
                @endphp

                <{{ $cardTag }}
                    @if($isClickable)
                        href="{{ $cardLink }}"
                        wire:navigate
                        class="group relative block bg-white rounded-xl border-2 border-black p-5 hover:shadow-md transition-shadow duration-200"
                    @else
                        class="group relative block bg-white rounded-xl border-2 border-black p-5 hover:shadow-md transition-shadow duration-200"
                    @endif
                >
                    {{-- Clickable Notification Badge --}}
                    @if($isClickable)
                        <div class="absolute top-4 right-4 md:right-auto md:left-1/2 md:-translate-x-1/2 z-10">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide border animate-pulse
                                {{ $b->status == 'approved' ? 'bg-green-100 text-green-800 border-green-300' : 'bg-indigo-100 text-indigo-800 border-indigo-300' }}">
                                <x-heroicon-o-arrow-up-tray class="w-3 h-3"/>
                                {{ $b->status == 'approved' ? 'Upload Before Photo' : 'Upload After Photo' }}
                            </span>
                        </div>
                    @endif

                    <div class="flex flex-col md:flex-row md:items-start justify-between gap-4 mb-3">
                        <div class="flex-1 min-w-0">
                            <h3 class="text-lg font-bold text-gray-900 mb-2 truncate group-hover:text-blue-600 transition-colors">
                                {{ $b->purpose ? ucfirst($b->purpose) : 'Vehicle Booking' }}
                            </h3>

                            <div class="flex flex-wrap items-center gap-2 text-xs">
                                <span class="font-mono font-medium text-gray-800 inline-flex items-center gap-1 bg-gray-100 px-2 py-1 rounded">
                                    <x-heroicon-o-hashtag class="w-3 h-3"/> {{ $b->vehiclebooking_id }}
                                </span>

                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md border border-gray-200 bg-gray-50 text-gray-700 font-medium">
                                    <x-heroicon-o-truck class="w-3 h-3"/>
                                    {{ $vehicleName }}
                                </span>

                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md border border-gray-200 bg-gray-50 text-gray-700 font-medium">
                                    <x-heroicon-o-calendar-days class="w-3 h-3"/>
                                    {{ $dateStr }}
                                </span>

                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md border border-gray-200 bg-gray-50 text-gray-700 font-medium">
                                    <x-heroicon-o-clock class="w-3 h-3"/>
                                    {{ $timeStr }}
                                </span>
                            </div>
                        </div>

                        <span class="shrink-0 inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide border {{ $statusConfig['class'] }}">
                            @if($statusConfig['icon'] === 'clock') <x-heroicon-o-clock class="w-3.5 h-3.5"/>
                            @elseif($statusConfig['icon'] === 'check-circle') <x-heroicon-o-check-circle class="w-3.5 h-3.5"/>
                            @elseif($statusConfig['icon'] === 'play-circle') <x-heroicon-o-play-circle class="w-3.5 h-3.5"/>
                            @elseif($statusConfig['icon'] === 'x-circle') <x-heroicon-o-x-circle class="w-3.5 h-3.5"/>
                            @elseif($statusConfig['icon'] === 'no-symbol') <x-heroicon-o-no-symbol class="w-3.5 h-3.5"/>
                            @else <x-heroicon-o-archive-box class="w-3.5 h-3.5"/>
                            @endif
                            {{ str_replace('_',' ', ucfirst($b->status)) }}
                        </span>
                    </div>

                    {{-- Details Grid --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-2 mb-4 pt-3 border-t border-dashed border-gray-200">
                        @if(!empty($b->borrower_name))
                            <div class="flex flex-col">
                                <span class="text-[10px] uppercase tracking-wider text-gray-500 font-semibold">Borrower</span>
                                <span class="text-sm text-gray-900 font-medium">{{ $b->borrower_name }}</span>
                            </div>
                        @endif
                        @if(!empty($b->destination))
                            <div class="flex flex-col">
                                <span class="text-[10px] uppercase tracking-wider text-gray-500 font-semibold">Destination</span>
                                <span class="text-sm text-gray-900 font-medium truncate">{{ $b->destination }}</span>
                            </div>
                        @endif
                        @if(isset($b->odd_even_area))
                            <div class="flex flex-col">
                                <span class="text-[10px] uppercase tracking-wider text-gray-500 font-semibold">Odd/Even Area</span>
                                <span class="text-sm text-gray-900 font-medium">{{ ucfirst($b->odd_even_area) }}</span>
                            </div>
                        @endif
                        @if(!empty($b->purpose_type))
                            <div class="flex flex-col">
                                <span class="text-[10px] uppercase tracking-wider text-gray-500 font-semibold">Type</span>
                                <span class="text-sm text-gray-900 font-medium">{{ ucfirst($b->purpose_type) }}</span>
                            </div>
                        @endif
                    </div>

                    {{-- Notes / Rejection --}}
                    @if($b->status === 'rejected' && !empty($b->notes))
                        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3">
                            <div class="text-xs font-bold text-red-800 inline-flex items-center gap-1 mb-1">
                                <x-heroicon-o-exclamation-circle class="w-4 h-4"/>
                                Rejection Reason
                            </div>
                            <div class="text-sm text-red-700">{{ $b->notes }}</div>
                        </div>
                    @elseif(!empty($b->notes))
                        <div class="mb-4 rounded-lg border border-gray-200 bg-gray-50 p-3">
                            <div class="text-xs font-bold text-gray-600 inline-flex items-center gap-1 mb-1">
                                <x-heroicon-o-chat-bubble-left-ellipsis class="w-4 h-4"/>
                                Note
                            </div>
                            <div class="text-sm text-gray-700">{{ $b->notes }}</div>
                        </div>
                    @endif

                    {{-- Footer (Photos & Dates) --}}
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-3 border-t border-gray-100">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-gray-100 text-xs font-medium text-gray-600 border border-gray-200" title="Before Photos">
                                <x-heroicon-o-camera class="w-3.5 h-3.5"/>
                                Before: {{ $beforeC }}
                            </span>
                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-gray-100 text-xs font-medium text-gray-600 border border-gray-200" title="After Photos">
                                <x-heroicon-o-camera class="w-3.5 h-3.5"/>
                                After: {{ $afterC }}
                            </span>
                        </div>

                        <div class="text-[10px] text-gray-400 flex items-center gap-3">
                            <div class="flex items-center gap-1">
                                <x-heroicon-o-pencil-square class="w-3 h-3"/>
                                Created {{ optional($b->created_at)->format('d M Y') }}
                            </div>
                            @if($b->updated_at != $b->created_at)
                                <div class="flex items-center gap-1">
                                    <x-heroicon-o-arrow-path class="w-3 h-3"/>
                                    Updated {{ optional($b->updated_at)->diffForHumans() }}
                                </div>
                            @endif
                        </div>
                    </div>
                </{{ $cardTag }}>
            @empty
                <div class="rounded-xl border-2 border-dashed border-gray-300 p-12 text-center">
                    <x-heroicon-o-truck class="mx-auto h-12 w-12 text-gray-400" />
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No bookings found</h3>
                    <p class="mt-1 text-sm text-gray-500">Try adjusting the filters above.</p>
                </div>
            @endforelse
        </div>

        @if(method_exists($bookings, 'links'))
            <div class="mt-6">
                {{ $bookings->links() }}
            </div>
        @endif
    </div>
</div>