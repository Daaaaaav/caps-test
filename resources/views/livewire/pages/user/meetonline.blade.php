<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    {{-- Header --}}
    <div class="bg-white rounded-xl shadow-sm border-2 border-black p-4 md:p-6 mb-4 md:mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <h1 class="text-xl md:text-2xl font-bold text-gray-900">Online Meeting Booking</h1>

            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
                <div class="inline-flex rounded-lg overflow-hidden bg-gray-100 border border-gray-200">
                    <a href="{{ route('book-room') }}"
                        class="px-3 md:px-4 py-2 text-sm font-medium transition-colors border-r border-gray-200 inline-flex items-center gap-1.5
                  {{ request()->routeIs('book-room') ? 'bg-gray-900 text-white' : 'text-gray-700 hover:text-gray-900' }}">
                        Offline (Room)
                    </a>
                    <a href="{{ route('user.meetonline') }}"
                        class="px-3 md:px-4 py-2 text-sm font-medium transition-colors inline-flex items-center gap-1.5
                  bg-gray-900 text-white">
                        Online Meeting
                    </a>
                </div>

                <div class="inline-flex rounded-lg overflow-hidden bg-gray-100 border border-gray-200">
                    <button wire:click="switchView('form')"
                        class="px-3 md:px-4 py-2 text-sm font-medium transition-colors border-r border-gray-200 inline-flex items-center gap-1.5 {{ $view === 'form' ? 'bg-gray-900 text-white' : 'text-gray-600 hover:text-gray-900' }}">
                        Form
                    </button>
                    <button wire:click="switchView('calendar')"
                        class="px-3 md:px-4 py-2 text-sm font-medium transition-colors inline-flex items-center gap-1.5 {{ $view === 'calendar' ? 'bg-gray-900 text-white' : 'text-gray-600 hover:text-gray-900' }}">
                        Calendar
                    </button>
                </div>
            </div>
        </div>
    </div>

    @if ($view === 'form')
    {{-- Standard Form View --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border-2 border-black p-4 md:p-5">
                <h2 class="text-lg font-semibold text-gray-900 mb-2">Book an Online Meeting</h2>
                <p class="text-sm text-gray-600 mb-6">Pilih Zoom atau Google Meet. Link muncul setelah disetujui receptionist.</p>

                <form wire:submit.prevent="submit" class="space-y-5">
                    <div>
                        <label class="block text-xs font-medium text-gray-900 mb-1.5">Meeting Title</label>
                        <input type="text" wire:model="meeting_title"
                            class="w-full px-3 py-2 text-sm text-gray-900 placeholder:text-gray-400 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent" />
                        @error('meeting_title') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-900 mb-1.5">Platform</label>
                            <select wire:model="online_provider"
                                class="w-full px-3 py-2 text-sm border text-gray-900 border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent">
                                <option value="">Pilih platform</option>
                                <option value="zoom">Zoom</option>
                                <option value="google_meet">Google Meet</option>
                            </select>
                            @error('online_provider') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-900 mb-1.5">Tanggal</label>
                            <input type="date" wire:model="date"
                                class="w-full px-3 py-2 text-sm border text-gray-900 border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent" />
                            @error('date') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="hidden md:block"></div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-900 mb-1.5">Start Time</label>
                            <input type="time" wire:model="start_time"
                                class="w-full px-3 py-2 text-sm border text-gray-900 border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent" />
                            @error('start_time') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-900 mb-1.5">End Time</label>
                            <input type="time" wire:model="end_time"
                                class="w-full px-3 py-2 text-sm border text-gray-900 border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent" />
                            @error('end_time') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- ADDED CHECKBOX HERE (Main Form) --}}
                    <div class="pt-2">
                        <label class="inline-flex items-start gap-3">
                            <input type="checkbox" wire:model="informInfo"
                                class="mt-0.5 rounded border-gray-300 text-gray-900 focus:ring-gray-900">
                            <span class="text-sm text-gray-700">
                                Minta Information Dept menginformasikan meeting ini (akan disimpan sebagai <span class="font-semibold text-gray-900">request</span>)
                            </span>
                        </label>
                    </div>

                    <div class="flex gap-4 pt-4 border-t border-gray-100">
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
            {{-- Right Side Cards --}}
            <div class="bg-blue-50  rounded-xl shadow-sm border-2 border-black p-4 md:p-5">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Platform Tips</h3>
                <ul class="text-xs md:text-sm text-gray-700 list-disc pl-5 space-y-1">
                    <li>Zoom cocok untuk webinar / breakout rooms.</li>
                    <li>Google Meet praktis untuk Google Workspace.</li>
                    <li>Host sebaiknya hadir 5 menit lebih awal.</li>
                </ul>
            </div>

            <div class="bg-white rounded-xl shadow-sm border-2 border-black p-4 md:p-5">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Status Booking Saya</h3>
                <div class="space-y-3">
                    @forelse($bookings as $b)
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex-1">
                                <h4 class="font-medium text-sm text-gray-900">{{ $b->meeting_title }}</h4>
                                <p class="text-xs text-gray-600 mt-1">
                                    <x-heroicon-o-calendar class="w-3 h-3 inline mr-0.5" />
                                    {{ \Carbon\Carbon::parse($b->date)->format('d M Y') }},
                                    <x-heroicon-o-clock class="w-3 h-3 inline mr-0.5" />
                                    {{ \Carbon\Carbon::parse($b->start_time)->timezone('Asia/Jakarta')->format('H:i') }}–{{ \Carbon\Carbon::parse($b->end_time)->timezone('Asia/Jakarta')->format('H:i') }}
                                    • {{ ucfirst(str_replace('_', ' ', $b->online_provider)) }}
                                    @if($b->requestinformation === 'request')
                                    <span class="ml-1 text-blue-600">• Info Req</span>
                                    @endif
                                </p>

                                @if(($b->status ?? null) === 'approved' && ($b->online_meeting_url ?? null))
                                <div x-data="{ copied:false }" class="mt-2 flex items-center gap-2">
                                    <a href="{{ $b->online_meeting_url }}" target="_blank"
                                        class="text-blue-600 underline text-xs inline-flex items-center gap-1">
                                        <x-heroicon-o-link class="w-3 h-3" />
                                        Join Meeting
                                    </a>
                                    <button type="button"
                                        class="text-[10px] px-2 py-0.5 border border-gray-300 rounded hover:bg-gray-50"
                                        @click="navigator.clipboard.writeText('{{ $b->online_meeting_url }}'); copied=true; setTimeout(()=>copied=false,1500)">
                                        Copy link
                                    </button>
                                    <span x-show="copied" x-cloak class="text-[10px] text-green-600">Copied!</span>
                                </div>
                                @endif
                            </div>

                            <div class="flex flex-col items-end gap-1">
                                <span class="shrink-0 text-[10px] font-semibold px-2 py-0.5 rounded
                                            {{ ($b->status ?? '') === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                                            (($b->status ?? '') === 'approved' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800') }}">
                                    {{ ucfirst($b->status ?? 'pending') }}
                                </span>
                            </div>
                        </div>
                    </div>
                    @empty
                    <p class="text-sm text-gray-500">Belum ada booking.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
    @else
    {{-- Calendar View (Grid) --}}
    <div wire:poll.60s class="bg-white rounded-xl shadow-sm border-2 border-black overflow-hidden">
        <div class="bg-gray-50 border-b-2 border-black/10 p-4">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Online Meeting Schedule</h2>
                    <p class="text-sm text-gray-600">{{ \Carbon\Carbon::parse($date)->format('l, F j, Y') }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <button wire:click="previousMonth" class=" md:inline px-3 py-1.5 border border-gray-300 bg-white rounded-md text-xs font-medium hover:bg-gray-50 transition-colors inline-flex items-center gap-1.5">
                        <x-heroicon-o-chevron-double-left class="w-3 h-3" /> Month
                    </button>
                    <button wire:click="previousWeek" class=" md:inline px-3 py-1.5 border border-gray-300 bg-white rounded-md text-xs font-medium hover:bg-gray-50 transition-colors inline-flex items-center gap-1.5">
                        <x-heroicon-o-chevron-left class="w-3 h-3" /> Week
                    </button>
                    <input type="date" wire:model.live="date" wire:change="selectDate($event.target.value)"
                        class="px-3 py-1.5 border border-gray-300 rounded-md text-xs font-medium focus:outline-none focus:ring-2 focus:ring-gray-900" />
                    <button wire:click="nextWeek" class=" md:inline px-3 py-1.5 border border-gray-300 bg-white rounded-md text-xs font-medium hover:bg-gray-50 transition-colors inline-flex items-center gap-1.5">
                        Week <x-heroicon-o-chevron-right class="w-3 h-3" />
                    </button>
                    <button wire:click="nextMonth" class=" md:inline px-3 py-1.5 border border-gray-300 bg-white rounded-md text-xs font-medium hover:bg-gray-50 transition-colors inline-flex items-center gap-1.5">
                        Month <x-heroicon-o-chevron-double-right class="w-3 h-3" />
                    </button>
                </div>
            </div>
        </div>

        <div class="relative">
            <div class="flex">
                {{-- START: Time Column (Fixed Width) --}}
                {{-- The time column now handles the right border, but not the bottom border on the time labels themselves --}}
                <div class="w-16 md:w-20 shrink-0 border-r border-gray-200 bg-gray-50 sticky left-0 z-10">
                    <div class="h-10 border-b border-gray-200 bg-gray-100"></div>
                    @foreach($timeSlots as $t)
                    {{-- programmer's simple documentation: Removed border-b here; main grid rows handle the horizontal border for alignment. Reverted to full centering. --}}
                    <div class="h-8 text-[10px] text-gray-500 font-medium flex items-center justify-center">
                        {{ $t }}
                    </div>
                    @endforeach
                </div>
                {{-- END: Time Column --}}

                <div class="overflow-x-auto">
                    <div class="min-w-[480px]">
                        <div class="grid" style="grid-template-columns: repeat({{ count($providers) }}, minmax(200px,1fr));">
                            @foreach($providers as $p)
                            <div class="h-10 bg-gray-50 border-b border-r border-gray-200 px-3 flex items-center justify-center">
                                <div class="flex items-center gap-2">
                                    <div class="w-2 h-2 rounded-full bg-gray-600"></div>
                                    <span class="text-xs font-bold text-gray-700 truncate">{{ $p['label'] }}</span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @foreach($timeSlots as $t)
                        {{-- programmer's simple documentation: The main grid rows now consistently provide the border-b for perfect horizontal alignment with the time column --}}
                        <div class="grid border-b border-gray-100"
                            style="grid-template-columns: repeat({{ count($providers) }}, minmax(200px,1fr));">
                            @foreach($providers as $p)
                            @php
                            $slotBooking = $this->getOnlineBookingForSlot($p['key'], $date, $t);
                            // programmer's simple documentation: Check if the current slot is in the past using Indonesia time
                            $currentDateTime = \Carbon\Carbon::now();
                            $slotDateTime = \Carbon\Carbon::parse($date . ' ' . $t)->setTimezone('Asia/Jakarta');
                            $isPastSlot = $slotDateTime->lt($currentDateTime);
                            @endphp
                            <div class="h-8 relative border-r border-gray-100">
                                @if($slotBooking)
                                <div class="w-full h-full bg-red-100 flex items-center justify-center px-2 cursor-not-allowed"
                                    title="{{ $slotBooking['meeting_title'] }}">
                                    <span class="truncate text-[10px] text-red-900 font-medium w-full text-center">
                                        {{ $slotBooking['meeting_title'] }}
                                    </span>
                                </div>
                                @elseif($isPastSlot)
                                {{-- Style for Past Slot --}}
                                <div class="w-full h-full bg-gray-100 flex items-center justify-center px-2 cursor-not-allowed">
                                    <span class="text-[10px] text-gray-400 font-medium">
                                        Past
                                    </span>
                                </div>
                                @else
                                <button wire:click="selectCalendarSlot('{{ $p['key'] }}', '{{ $date }}', '{{ $t }}')"
                                    class="w-full h-full bg-white hover:bg-green-200 transition-colors group flex items-center justify-center cursor-pointer">
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
            <div class="flex items-center gap-4 text-xs font-medium text-gray-600">
                <span class="inline-flex items-center gap-2">
                    <span class="w-3 h-3 bg-red-100 border border-red-200 rounded inline-block"></span> Booked
                </span>
                {{-- Updated Legend --}}
                <span class="inline-flex items-center gap-2">
                    <span class="w-3 h-3 bg-gray-100 border border-gray-200 rounded inline-block"></span> Past/Unavailable
                </span>
                <span class="inline-flex items-center gap-2">
                    <span class="w-3 h-3 bg-white border border-gray-200 rounded inline-block"></span> Available
                </span>
                <span class="inline-flex items-center gap-2">
                    <span class="w-3 h-3 bg-green-200 border border-green-300 rounded inline-block"></span> Click to book
                </span>
            </div>
        </div>
    </div>
    @endif

    {{-- THE QUICK BOOKING MODAL --}}
    @if($showQuickModal)
    <div class="fixed inset-0 z-[100]">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-opacity"
            wire:click="closeQuickModal"></div>

        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="w-full max-w-lg bg-white rounded-2xl border border-gray-200 shadow-2xl overflow-hidden transform transition-all">
                <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between bg-white">
                    <div>
                        <h3 class="font-bold text-gray-900 text-lg">
                            Book {{ collect($providers)->firstWhere('key', $online_provider)['label'] ?? 'Meeting' }}
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
                        <input type="text" wire:model.defer="meeting_title"
                            placeholder="Enter meeting title..."
                            class="w-full h-10 px-3 rounded-xl border-gray-200 focus:border-gray-900 focus:ring-gray-900 sm:text-sm placeholder-gray-400 transition-colors">
                        @error('meeting_title') <p class="text-sm text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Start Time</label>
                            <div class="w-full h-10 px-3 flex items-center bg-gray-50 rounded-xl border border-gray-200 text-gray-500 sm:text-sm">
                                {{ $start_time }}
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">End Time</label>
                            <input type="time" wire:model.live="end_time" min="{{ $start_time }}"
                                class="w-full h-10 px-3 rounded-xl border-gray-200 focus:border-gray-900 focus:ring-gray-900 sm:text-sm">
                            @error('end_time') <p class="text-sm text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- ADDED CHECKBOX HERE (Modal) --}}
                    <div class="flex items-start p-3 border border-gray-100 rounded-xl bg-gray-50/50">
                        <div class="flex items-center h-5">
                            <input id="notify_info_modal"
                                type="checkbox"
                                wire:model="informInfo"
                                class="rounded border-gray-300 text-gray-900 focus:ring-gray-900 h-4 w-4">
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="notify_info_modal" class="font-medium text-gray-700 cursor-pointer">Notify Information Dept</label>
                            <p class="text-gray-500 text-xs">Request support from the information department.</p>
                        </div>
                    </div>

                    <div class="bg-blue-50 rounded-xl p-3 border border-blue-100 flex gap-3">
                        <div class="text-blue-600">
                            {{-- Original SVG was an Information Circle / Exclamation Icon --}}
                            <x-heroicon-o-information-circle class="h-5 w-5" />
                        </div>
                        <p class="text-xs text-blue-800">
                            Wait for receptionist approval to receive the meeting link.
                        </p>
                    </div>
                </div>

                <div class="px-5 py-4 border-t border-gray-200 bg-white flex items-center justify-end gap-3">
                    <button type="button" wire:click="closeQuickModal"
                        class="h-10 px-4 rounded-xl bg-white border border-gray-300 text-gray-700 text-sm font-medium hover:bg-gray-50 focus:outline-none transition-colors">
                        Cancel
                    </button>
                    <button type="button" wire:click="submit"
                        class="h-10 px-6 rounded-xl bg-gray-900 text-white text-sm font-bold hover:bg-gray-800 focus:outline-none shadow-md transition-all transform active:scale-95">
                        Confirm Request
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>