<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    {{-- Header --}}
    <div class="bg-white rounded-xl shadow-sm border-2 border-black p-4 md:p-6 mb-4 md:mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <h1 class="text-xl md:text-2xl font-bold text-gray-900">Room Booking System</h1>

            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
                <div class="inline-flex rounded-lg overflow-hidden bg-gray-100 border border-gray-200">
                    <a href="{{ route('book-room') }}"
                        @class([ 'px-3 md:px-4 py-2 text-sm font-medium transition-colors border-r border-gray-200 inline-flex items-center gap-1.5' , 'bg-gray-900' , 'text-white' ,
                        ])>
                        Offline (Room)
                    </a>
                    <a href="{{ route('user.meetonline') }}"
                        @class([ 'px-3 md:px-4 py-2 text-sm font-medium transition-colors inline-flex items-center gap-1.5' , 'bg-gray-900 text-white'=> request()->routeIs('user.meetonline'),
                        'text-gray-700 hover:text-gray-900' => !request()->routeIs('user.meetonline'),
                        ])>
                        Online Meeting
                    </a>
                </div>

                <div class="inline-flex rounded-lg overflow-hidden bg-gray-100 border border-gray-200">
                    <button wire:click="switchView('form')"
                        @class([ 'px-3 md:px-4 py-2 text-sm font-medium transition-colors border-r border-gray-200 inline-flex items-center gap-1.5' , 'bg-gray-900 text-white'=> $view === 'form',
                        'text-gray-600 hover:text-gray-900' => $view !== 'form',
                        ])>
                        Form
                    </button>
                    <button wire:click="switchView('calendar')"
                        @class([ 'px-3 md:px-4 py-2 text-sm font-medium transition-colors inline-flex items-center gap-1.5' , 'bg-gray-900 text-white'=> $view === 'calendar',
                        'text-gray-600 hover:text-gray-900' => $view !== 'calendar',
                        ])>
                        Calendar
                    </button>
                </div>
            </div>
        </div>
    </div>

    @if ($view === 'form')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl border-2 border-black/80 shadow-md p-4 md:p-5">
                <h2 class="text-lg font-semibold text-gray-900 mb-2">Book a Meeting Room</h2>
                <p class="text-sm text-gray-600 mb-6">Fill out the form below to request a room booking</p>

                <div class="bg-blue-50 mb-6 border border-blue-200 rounded-lg p-4 text-sm text-blue-800">
                    <h4 class="font-semibold mb-2 inline-flex items-center gap-1.5">
                        <x-heroicon-o-clock class="w-4 h-4" /> Booking Rules
                    </h4>
                    <ul class="list-disc pl-5 space-y-1 text-xs md:text-sm">
                        <li>Slot 30 menit.</li>
                        <li>Judul meeting jelas.</li>
                        <li>Minimal mulai 15 menit dari sekarang.</li>
                        <li>Jam yang lewat otomatis digeser ke slot berikutnya.</li>
                        <li>Tidak bisa pesan ke jam yang sudah lewat.</li>
                    </ul>
                </div>

                <form wire:submit.prevent="submitBooking" class="space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-900 mb-1.5">Meeting Title</label>
                            <input type="text" wire:model.defer="meeting_title" placeholder="Enter meeting title"
                                class="w-full px-3 py-2 text-sm text-gray-900 placeholder:text-gray-400 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent">
                            @error('meeting_title') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-900 mb-1.5">Room</label>
                            <select wire:model="room_id"
                                class="w-full px-3 py-2 text-sm text-gray-900 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent">
                                <option value="">Select room</option>
                                @foreach($rooms as $room)
                                <option value="{{ $room['id'] }}" {{ !$room['available_req'] ? 'disabled' : '' }}>
                                    {{ $room['name'] }} {{ !$room['available_req'] ? '(Occupied)' : '' }}
                                </option>
                                @endforeach
                            </select>
                            @error('room_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-900 mb-1.5">Date</label>
                            <input type="date" wire:model.live="date" wire:change="selectDate($event.target.value)"
                                class="w-full px-3 py-2 text-sm text-gray-900 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent">
                            @error('date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-900 mb-1.5">Number of Attendees</label>
                            <input type="number" wire:model.defer="number_of_attendees" placeholder="0" min="1"
                                class="w-full px-3 py-2 text-sm text-gray-900 placeholder:text-gray-400 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent">
                            @error('number_of_attendees') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-900 mb-1.5">Start Time</label>
                            <input type="time" wire:model.live="start_time" min="{{ $minStart }}"
                                class="w-full px-3 py-2 text-sm text-gray-900 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent">
                            @error('start_time') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-900 mb-1.5">End Time</label>
                            <input type="time" wire:model.live="end_time" min="{{ $start_time ?: $minStart }}"
                                class="w-full px-3 py-2 text-sm text-gray-900 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent">
                            @error('end_time') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-900 mb-2">Additional Requirements</label>
                        <div class="grid grid-cols-2 gap-3">
                            @forelse ($requirementsMaster as $reqName)
                            <label class="flex items-center space-x-2">
                                <input type="checkbox" wire:model.live="requirements" value="{{ $reqName }}"
                                    class="rounded border-gray-300 text-gray-900 focus:ring-gray-900">
                                <span class="text-sm text-gray-700">{{ $reqName }}</span>
                            </label>
                            @empty
                            <p class="text-xs text-gray-500 col-span-2">Belum ada requirements untuk perusahaan Anda.</p>
                            @endforelse

                            <label class="flex items-center space-x-2">
                                <input type="checkbox" wire:model.live="requirements" value="other"
                                    class="rounded border-gray-300 text-gray-900 focus:ring-gray-900">
                                <span class="text-sm text-gray-700">Other</span>
                            </label>
                        </div>
                    </div>

                    @if (in_array('other', $requirements ?? [], true))
                    <div class="mt-4">
                        <label class="block text-xs font-medium text-gray-900 mb-1.5">Special Notes</label>
                        <textarea wire:model.defer="special_notes" rows="3" placeholder="Please specify your other requirement…"
                            class="w-full px-3 py-2 text-sm text-gray-900 placeholder:text-gray-400 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent resize-none"></textarea>
                        @error('special_notes') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    @endif

                    <div class="pt-2">
                        <label class="inline-flex items-start gap-3">
                            <input type="checkbox" wire:model.live="informInfo"
                                class="mt-0.5 rounded border-gray-300 text-gray-900 focus:ring-gray-900">
                            <span class="text-sm text-gray-700">
                                Minta Information Dept menginformasikan meeting ini (akan disimpan sebagai <span class="font-semibold text-gray-900">request</span>)
                            </span>
                        </label>
                    </div>

                    <div class="flex space-x-4 pt-4 border-t border-gray-100">
                        <button type="button" wire:click="$refresh"
                            class="px-4 py-2 text-sm font-medium border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors inline-flex items-center gap-1.5">
                            <x-heroicon-o-arrow-path class="w-4 h-4" />
                            Clear Form
                        </button>
                        <button type="submit"
                            class="px-4 py-2 text-sm font-medium bg-gray-900 text-white rounded-lg hover:bg-gray-800 transition-colors inline-flex items-center gap-1.5">
                            <x-heroicon-o-check-circle class="w-4 h-4" />
                            Submit Request
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="space-y-6">
            {{-- Availability Card --}}
            <div wire:poll.60s class="bg-white rounded-xl border-2 border-black/80 shadow-md p-4 md:p-5">
                <h3 class="text-lg font-semibold text-gray-900 mb-1">Room Availability</h3>
                <p class="text-xs text-gray-600 mb-4">
                    For {{ \Carbon\Carbon::parse($date)->format('l, F j, Y') }}
                    @if($start_time && $end_time) — {{ $start_time }}–{{ $end_time }} @endif
                </p>
                <div class="space-y-3">
                    @foreach($rooms as $room)
                    <div class="flex items-center justify-between p-3 {{ $room['available_req'] ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200' }} border rounded-lg">
                        <div class="flex items-center space-x-2">
                            <div class="w-2 h-2 {{ $room['available_req'] ? 'bg-green-500' : 'bg-red-500' }} rounded-full"></div>
                            <span class="font-medium text-sm text-gray-900">{{ $room['name'] }}</span>
                        </div>
                        <span class="text-xs font-bold uppercase {{ $room['available_req'] ? 'text-green-700' : 'text-red-700' }}">
                            {{ $room['available_req'] ? 'Available' : 'Occupied' }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Recent Bookings Card --}}
            <div class="bg-white rounded-xl border-2 border-black/80 shadow-md p-4 md:p-5">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Bookings</h3>
                <div class="space-y-4">
                    @foreach(array_slice($bookings, 0, 3) as $booking)
                    <div class="flex items-start space-x-3 pb-3 border-b border-gray-100 last:border-0 last:pb-0">
                        <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center flex-shrink-0 border border-gray-200">
                            <x-heroicon-o-calendar class="w-4 h-4 text-gray-600" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2">
                                <h4 class="font-medium text-sm text-gray-900 truncate">{{ $booking['meeting_title'] }}</h4>
                                @if(isset($booking['status']))
                                @if($booking['status'] === 'approved')
                                <span class="shrink-0 text-[10px] font-semibold text-green-800 bg-green-100 px-2 py-0.5 rounded">Approved</span>
                                @elseif($booking['status'] === 'pending')
                                <span class="shrink-0 text-[10px] font-semibold text-yellow-800 bg-yellow-100 px-2 py-0.5 rounded">Pending</span>
                                @elseif($booking['status'] === 'rejected')
                                <span class="shrink-0 text-[10px] font-semibold text-red-800 bg-red-100 px-2 py-0.5 rounded">Rejected</span>
                                @else
                                <span class="shrink-0 text-[10px] font-semibold text-green-800 bg-green-100 px-2 py-0.5 rounded">Completed</span>
                                @endif
                                @endif
                            </div>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ \Carbon\Carbon::parse($booking['date'])->format('M j') }},
                                {{ \Carbon\Carbon::parse($booking['start_time'])->format('H:i') }} •
                                {{ collect($rooms)->firstWhere('id', $booking['room_id'])['name'] ?? 'Unknown Room' }}
                                @if(!empty($booking['requestinformation']))
                                <span class="ml-1 text-xs text-blue-600">• Info Req</span>
                                @endif
                            </p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    @else
    {{-- Calendar View (FIXED ALIGNMENT) --}}
    <div wire:poll.60s class="bg-white rounded-xl border-2 border-black/80 shadow-md overflow-hidden">
        <div class="bg-gray-50 border-b-2 border-black/10 p-4">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Room Schedule</h2>
                    <p class="text-sm text-gray-600">{{ \Carbon\Carbon::parse($date)->format('l, F j, Y') }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <button wire:click="previousMonth" class=" md:inline px-3 py-1.5 border border-gray-300 bg-white rounded-md text-xs font-medium hover:bg-gray-50 transition-colors inline-flex items-center gap-1.5">
                        <x-heroicon-o-chevron-double-left class="w-3 h-3" />
                        Month
                    </button>
                    <button wire:click="previousWeek" class=" md:inline px-3 py-1.5 border border-gray-300 bg-white rounded-md text-xs font-medium hover:bg-gray-50 transition-colors inline-flex items-center gap-1.5">
                        <x-heroicon-o-chevron-left class="w-3 h-3" />
                        Week
                    </button>

                    {{-- Day Navigation --}}
                    <button wire:click="previousDay" class="px-3 py-1.5 border border-gray-300 bg-white rounded-md text-xs font-medium hover:bg-gray-50 transition-colors">
                        <x-heroicon-o-chevron-left class="w-4 h-4" />
                    </button>
                    <input type="date" wire:model.live="date" wire:change="selectDate($event.target.value)" class="px-3 py-1.5 border border-gray-300 rounded-md text-xs font-medium focus:outline-none focus:ring-2 focus:ring-gray-900" />
                    <button wire:click="nextDay" class="px-3 py-1.5 border border-gray-300 bg-white rounded-md text-xs font-medium hover:bg-gray-50 transition-colors">
                        <x-heroicon-o-chevron-right class="w-4 h-4" />
                    </button>

                    <button wire:click="nextWeek" class="md:inline px-3 py-1.5 border border-gray-300 bg-white rounded-md text-xs font-medium hover:bg-gray-50 transition-colors inline-flex items-center gap-1.5">
                        Week
                        <x-heroicon-o-chevron-right class="w-3 h-3" />
                    </button>
                    <button wire:click="nextMonth" class="md:inline px-3 py-1.5 border border-gray-300 bg-white rounded-md text-xs font-medium hover:bg-gray-50 transition-colors inline-flex items-center gap-1.5">
                        Month
                        <x-heroicon-o-chevron-double-right class="w-3 h-3" />
                    </button>
                </div>
            </div>

            <div class="mt-4 flex items-center justify-between">
                <div class="text-xs text-gray-500 font-medium">
                    Showing rooms {{ ($roomsPage - 1) * $roomsPerPage + 1 }} –
                    {{ min($roomsPage * $roomsPerPage, count($rooms)) }}
                    of {{ count($rooms) }}
                </div>
                <div class="flex gap-2">
                    <button wire:click="prevRoomPage" class="px-3 py-1.5 border border-gray-300 bg-white rounded-md text-xs font-medium hover:bg-gray-50 disabled:opacity-50" {{ $roomsPage <= 1 ? 'disabled' : '' }}>
                        <x-heroicon-o-chevron-left class="w-3 h-3" /> Rooms
                    </button>
                    <button wire:click="nextRoomPage" class="px-3 py-1.5 border border-gray-300 bg-white rounded-md text-xs font-medium hover:bg-gray-50 disabled:opacity-50" {{ $roomsPage >= $roomsTotalPages ? 'disabled' : '' }}>
                        Rooms <x-heroicon-o-chevron-right class="w-3 h-3" />
                    </button>
                </div>
            </div>
        </div>

        <div class="relative">
            <div class="flex">
                {{-- Time Column --}}
                {{-- programmer's simple documentation: Added 'hidden' class conditional on $showQuickModal to prevent the time column from showing through the modal backdrop. Also added 'border-b border-gray-100' to time labels for perfect vertical alignment with the grid slots --}}
                <div class="w-16 md:w-20 shrink-0 border-r border-gray-200 bg-gray-50 sticky left-0 z-10 @if($showQuickModal) hidden @endif">
                    <div class="h-10 border-b border-gray-200 bg-gray-100"></div>
                    @foreach($timeSlots as $t)
                    <div class="h-8 text-[10px] text-gray-500 font-medium flex items-center justify-center border-b border-gray-100">{{ $t }}</div>
                    @endforeach
                </div>

                <div class="overflow-x-auto">
                    <div class="min-w-[640px]">
                        {{-- Room Headers --}}
                        <div class="grid" style="grid-template-columns: repeat({{ count($visibleRooms) }}, minmax(160px,1fr));">
                            @foreach($visibleRooms as $room)
                            <div class="h-10 bg-gray-50 border-b border-r border-gray-200 px-3 flex items-center justify-center">
                                <div class="flex items-center gap-2">
                                    <div class="w-1.5 h-1.5 rounded-full {{ $room['available_req'] ? 'bg-green-500' : 'bg-red-500' }}"></div>
                                    <span class="text-xs font-bold text-gray-700 truncate">{{ $room['name'] }}</span>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        {{-- Grid Slots --}}
                        @foreach($timeSlots as $t)
                        {{-- programmer's simple documentation: Removed redundant 'border-b border-gray-100' from the grid row for clean alignment --}}
                        <div class="grid" style="grid-template-columns: repeat({{ count($visibleRooms) }}, minmax(160px,1fr));">
                            @foreach($visibleRooms as $room)
                            @php
                            $slotBooking = $this->getBookingForSlot($room['id'], $date, $t);
                            $slotCarbon = \Carbon\Carbon::parse($date . ' ' . $t, 'Asia/Jakarta');
                            // Passed logic: if slot is earlier than now + buffer
                            $isPast = $slotCarbon->lt(\Carbon\Carbon::now('Asia/Jakarta')->addMinutes(15));
                            @endphp

                            <div class="h-8 relative border-r border-gray-100">
                                @if($slotBooking)
                                {{-- Booked: Full Red Box (Non-clickable) --}}
                                <div class="w-full h-full bg-red-100 flex items-center justify-center px-2 cursor-not-allowed"
                                    title="{{ $slotBooking['meeting_title'] }} ({{ $slotBooking['start_time'] }} - {{ $slotBooking['end_time'] }})">
                                    <div class="truncate text-[10px] text-red-900 font-medium w-full text-center">
                                        {{ $slotBooking['meeting_title'] }}
                                    </div>
                                </div>

                                @elseif($isPast)
                                {{-- Past: Gray Box (Non-clickable) --}}
                                {{-- programmer's simple documentation: Added 'Past' text to the gray box --}}
                                <div class="w-full h-full bg-gray-100 flex items-center justify-center cursor-not-allowed">
                                    <span class="text-[10px] text-gray-400 font-medium">Past</span>
                                </div>

                                @else
                                {{-- Available: White Box, Hover Green, Reveal Book Text --}}
                                <button
                                    wire:click="selectCalendarSlot({{ $room['id'] }}, '{{ $date }}', '{{ $t }}')"
                                    class="w-full h-full bg-white hover:bg-green-200 transition-colors group flex items-center justify-center cursor-pointer"
                                    title="Book {{ $room['name'] }} at {{ $t }}">
                                    <span class="hidden group-hover:block text-[10px] font-bold text-green-800">Book</span>
                                </button>
                                @endif
                            </div>
                            @endforeach
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-gray-50 border-t border-gray-200 p-3">
            <div class="flex flex-wrap items-center gap-4 text-xs font-medium text-gray-600">
                <span class="inline-flex items-center gap-2"><span class="w-3 h-3 bg-red-100 border border-red-200 rounded inline-block"></span> Booked</span>
                <span class="inline-flex items-center gap-2"><span class="w-3 h-3 bg-gray-100 border border-gray-200 rounded inline-block"></span> Past/Closed</span>
                <span class="inline-flex items-center gap-2"><span class="w-3 h-3 bg-white border border-gray-200 rounded inline-block"></span> Available</span>
                <span class="inline-flex items-center gap-2"><span class="w-3 h-3 bg-green-200 border border-green-300 rounded inline-block"></span> Click to book</span>
            </div>
        </div>
    </div>
    @endif

    @if($showQuickModal)
    {{-- programmer's simple documentation: Changed z-index to z-[40] as requested. --}}
    <div class="fixed inset-0 **z-[40]**">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-opacity"
            wire:click="closeQuickModal"></div>

        <div class="absolute inset-0 flex items-center justify-center p-4">

            <div class="w-full max-w-lg bg-white rounded-2xl border border-gray-200 shadow-2xl overflow-hidden transform transition-all">

                <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between bg-white">
                    <div>
                        <h3 class="font-bold text-gray-900 text-lg">
                            {{ collect($rooms)->firstWhere('id', $room_id)['name'] ?? 'New Booking' }}
                        </h3>
                        <p class="text-xs text-gray-500 mt-0.5">
                            {{ \Carbon\Carbon::parse($date)->format('l, F j, Y') }}
                        </p>
                    </div>
                    <button type="button"
                        class="text-gray-400 hover:text-gray-600 transition-colors focus:outline-none p-1"
                        wire:click="closeQuickModal">
                        <x-heroicon-o-x-mark class="h-6 w-6" />
                    </button>
                </div>

                <div class="p-5 space-y-5 overflow-y-auto max-h-[75vh]">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Meeting Title</label>
                        <input type="text"
                            wire:model.defer="meeting_title"
                            placeholder="Enter meeting title..."
                            class="w-full h-10 px-3 rounded-xl border-gray-200 focus:border-gray-900 focus:ring-gray-900 sm:text-sm placeholder-gray-400 transition-colors">
                        @error('meeting_title')
                        <p class="text-sm text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Start</label>
                            <div class="w-full h-10 px-3 flex items-center bg-gray-50 rounded-xl border border-gray-200 text-gray-500 sm:text-sm">
                                {{ $start_time }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">End Time</label>
                            <input type="time"
                                wire:model.live="end_time"
                                min="{{ $start_time }}"
                                class="w-full h-10 px-3 rounded-xl border-gray-200 focus:border-gray-900 focus:ring-gray-900 sm:text-sm">
                            @error('end_time')
                            <p class="text-sm text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Attendees</label>
                            <input type="number"
                                wire:model.defer="number_of_attendees"
                                min="1"
                                placeholder="1"
                                class="w-full h-10 px-3 rounded-xl border-gray-200 focus:border-gray-900 focus:ring-gray-900 sm:text-sm">
                            @error('number_of_attendees')
                            <p class="text-sm text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    @if(count($requirementsMaster) > 0)
                    <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Requirements</label>
                        <div class="grid grid-cols-2 gap-y-3 gap-x-4">
                            @foreach($requirementsMaster as $req)
                            <label class="inline-flex items-center cursor-pointer group">
                                <input type="checkbox"
                                    wire:model.live="requirements"
                                    value="{{ $req }}"
                                    class="rounded border-gray-300 text-gray-900 focus:ring-gray-900 h-4 w-4 transition-colors">
                                <span class="ml-2 text-sm text-gray-600 group-hover:text-gray-900 select-none">{{ $req }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Special Notes</label>
                        <textarea wire:model.defer="special_notes"
                            rows="3"
                            placeholder="Optional instructions..."
                            class="w-full p-3 rounded-xl border-gray-200 focus:border-gray-900 focus:ring-gray-900 sm:text-sm resize-none"></textarea>
                    </div>

                    <div class="flex items-start p-3 border border-gray-100 rounded-xl bg-gray-50/50">
                        <div class="flex items-center h-5">
                            <input id="notify_info"
                                type="checkbox"
                                wire:model.live="informInfo"
                                class="mt-0.5 rounded border-gray-300 text-gray-900 focus:ring-gray-900">
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="notify_info" class="font-medium text-gray-700 cursor-pointer">Notify Information Dept</label>
                            <p class="text-gray-500 text-xs">Request support from the information department.</p>
                        </div>
                    </div>
                </div>

                <div class="px-5 py-4 border-t border-gray-200 bg-white flex items-center justify-end gap-3">
                    <button type="button"
                        wire:click="closeQuickModal"
                        class="h-10 px-4 rounded-xl bg-white border border-gray-300 text-gray-700 text-sm font-medium hover:bg-gray-50 focus:outline-none transition-colors">
                        Cancel
                    </button>
                    <button type="button"
                        wire:click="submitBooking"
                        class="h-10 px-6 rounded-xl bg-gray-900 text-white text-sm font-bold hover:bg-gray-800 focus:outline-none shadow-md transition-all transform active:scale-95">
                        Confirm Booking
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>