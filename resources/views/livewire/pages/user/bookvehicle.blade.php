{{-- A simple comment like an actual programmer's simple documentation --}}
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    {{-- Header --}}
    <div class="bg-white rounded-xl shadow-sm border-2 border-black p-4 md:p-6 mb-4 md:mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <h1 class="text-xl md:text-2xl font-bold text-gray-900">Vehicle Booking System</h1>
            <div class="inline-flex rounded-lg overflow-hidden bg-gray-100 border border-gray-200 self-start md:self-center">
                <span class="px-3 md:px-4 py-2 text-sm font-medium bg-gray-900 text-white cursor-default border-r border-gray-200">
                    Book Vehicle
                </span>
                <a href="{{ route('vehiclestatus') }}" wire:navigate
                   class="px-3 md:px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50 transition-colors">
                    Vehicle Status
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- LEFT: Booking Form --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border-2 border-black p-4 md:p-5">
                <h2 class="text-lg font-semibold text-gray-900 mb-2">
                    @if($booking)
                        Upload Photos - Booking #{{ $booking->vehiclebooking_id }}
                    @else
                        Book a Vehicle
                    @endif
                </h2>

                @if(session()->has('success'))
                    <div class="mb-4 rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800 flex items-center gap-2">
                        {{ session('success') }}
                    </div>
                @endif
                @if(session()->has('error'))
                    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800 flex items-center gap-2">
                        {{ session('error') }}
                    </div>
                @endif

                @if($booking)
                    {{-- Upload Mode (QUEUE SYSTEM) --}}
                    <div class="bg-gray-50 rounded-lg border border-gray-200 p-4 mb-6 text-sm space-y-2">
                        <div class="flex justify-between"><span class="text-gray-500">Booking ID:</span><span class="font-bold">#{{ $booking->vehiclebooking_id }}</span></div>
                        <div class="flex justify-between"><span class="text-gray-500">Vehicle:</span><span class="font-bold">{{ $booking->vehicle->name ?? 'N/A' }}</span></div>
                    </div>

                    <form wire:submit.prevent="handlePhotoUpload" enctype="multipart/form-data" class="space-y-5">
                        
                        {{-- AREA UPLOAD --}}
                        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                            <div class="flex justify-between items-center mb-3">
                                <label class="block text-sm font-bold text-gray-900">
                                    @if($booking->status === 'approved') 
                                        Foto Check-Out (Awal) 
                                    @else 
                                        Foto Check-In (Akhir) 
                                    @endif
                                    <span class="text-red-600">*</span>
                                </label>
                                <span class="text-[10px] font-medium bg-white px-2 py-1 rounded border text-gray-600">
                                    Total Dipilih: {{ count($collected_photos) }}
                                </span>
                            </div>

                            {{-- TOMBOL ADD PHOTOS --}}
                            <div class="mb-4">
                                <label for="file-queue-input" class="cursor-pointer group flex flex-col items-center justify-center w-full h-24 border-2 border-dashed border-gray-300 rounded-lg hover:bg-white hover:border-gray-400 transition-all">
                                    <div class="flex flex-col items-center justify-center pt-2 pb-3">
                                        {{-- ORIGINAL SVG: Cloud Upload (simple lines) --}}
                                        <x-heroicon-o-cloud-arrow-up class="w-6 h-6 mb-1 text-gray-400 group-hover:text-gray-600" />
                                        <p class="text-xs text-gray-500 group-hover:text-gray-700 text-center">
                                            <span class="font-bold">Klik untuk tambah foto</span><br>
                                            (Bisa pilih satu-satu atau sekaligus)
                                        </p>
                                    </div>
                                    {{-- INPUT HIDDEN: Mapped to temp_photos --}}
                                    <input id="file-queue-input" type="file" wire:model="temp_photos" multiple accept="image/*" class="hidden" />
                                </label>

                                {{-- Loading State saat memilih file --}}
                                <div wire:loading wire:target="temp_photos" class="w-full text-center mt-2">
                                    <span class="text-xs text-blue-600 font-medium animate-pulse">Memproses gambar...</span>
                                </div>

                                @error('temp_photos.*') <span class="text-xs text-red-600 block mt-1">{{ $message }}</span> @enderror
                                @error('collected_photos') <span class="text-xs text-red-600 block mt-1">{{ $message }}</span> @enderror
                            </div>

                            {{-- GALLERY PREVIEW GRID --}}
                            @if(!empty($collected_photos))
                                <div class="grid grid-cols-3 sm:grid-cols-4 gap-3">
                                    @foreach($collected_photos as $index => $photo)
                                        <div class="relative group aspect-square bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
                                            {{-- Image --}}
                                            @php
                                                $tempUrl = null;
                                                try {
                                                    $tempUrl = $photo->temporaryUrl();
                                                } catch (\Exception $e) {
                                                    // Silent error
                                                }
                                            @endphp

                                            @if($tempUrl)
                                                <img src="{{ $tempUrl }}" class="w-full h-full object-cover">
                                            @else
                                                <div class="flex items-center justify-center h-full bg-gray-100 text-[10px] text-red-500">
                                                    Error Loading
                                                </div>
                                            @endif
                                            
                                            {{-- Remove Button (X) --}}
                                            <button type="button" wire:click="removePhoto({{ $index }})" 
                                                class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 shadow hover:bg-red-600 transition-colors z-10"
                                                title="Hapus foto ini">
                                                {{-- ORIGINAL SVG: X Mark --}}
                                                <x-heroicon-o-x-mark class="h-3 w-3" />
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-4 bg-white border rounded-lg text-gray-400 text-xs italic">
                                    Belum ada foto yang ditambahkan.
                                </div>
                            @endif
                        </div>

                        <div class="flex gap-3 pt-2 justify-end">
                            <a href="{{ route('vehiclestatus') }}" class="px-4 py-2 border rounded-lg text-sm text-gray-700 hover:bg-gray-50">Cancel</a>
                            
                            <button type="submit" 
                                class="bg-gray-900 text-white px-6 py-2 rounded-lg text-sm hover:bg-gray-800 disabled:opacity-50 disabled:cursor-not-allowed"
                                @if(empty($collected_photos)) disabled @endif>
                                
                                <span wire:loading.remove wire:target="handlePhotoUpload">
                                    Upload {{ count($collected_photos) }} Foto
                                </span>
                                <span wire:loading wire:target="handlePhotoUpload" class="flex items-center gap-2">
                                    {{-- ORIGINAL SVG: Spinner --}}
                                    <x-heroicon-o-arrow-path class="animate-spin h-4 w-4 text-white" />
                                    Uploading...
                                </span>
                            </button>
                        </div>
                    </form>
                @else
                    {{-- Create Mode Form --}}
                    <form wire:submit.prevent="submitBooking" class="space-y-5">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- Name --}}
                            <div>
                                <label class="block text-xs font-medium text-gray-900 mb-1.5">Nama <span class="text-red-600">*</span></label>
                                @if($name)
                                    <div class="w-full px-3 py-2 text-sm text-gray-500 bg-gray-100 border border-gray-200 rounded-md cursor-not-allowed">{{ $name }}</div>
                                    <input type="hidden" wire:model="name" />
                                @else
                                    <input wire:model="name" type="text" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-gray-900 focus:outline-none">
                                @endif
                                @error('name') <div class="text-xs text-red-600 mt-1">{{ $message }}</div>@enderror
                            </div>

                            {{-- Dept --}}
                            <div>
                                <label class="block text-xs font-medium text-gray-900 mb-1.5">Departement <span class="text-red-600">*</span></label>
                                <select wire:model="department_id" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-gray-900 focus:outline-none">
                                    <option value="">Select department</option>
                                    @foreach($departments as $d) <option value="{{ $d->department_id }}">{{ $d->department_name }}</option> @endforeach
                                </select>
                                @error('department_id') <div class="text-xs text-red-600 mt-1">{{ $message }}</div>@enderror
                            </div>

                            {{-- MODE SWITCHER --}}
                            <div class="md:col-span-2 pt-2">
                                <label class="block text-xs font-bold text-gray-900 mb-2">Durasi Booking</label>
                                <div class="grid grid-cols-3 bg-gray-100 p-1 rounded-lg gap-1">
                                    <button type="button" wire:click="setBookingMode('perday')"
                                        class="text-sm font-medium py-2 rounded-md transition-all duration-200 {{ $booking_mode === 'perday' ? 'bg-white text-gray-900 shadow ring-1 ring-black/5 font-bold' : 'text-gray-500 hover:text-gray-700' }}">
                                        Full Day
                                        <span class="block text-[10px] font-normal opacity-75">08:00 - 17:00</span>
                                    </button>
                                    <button type="button" wire:click="setBookingMode('24hours')"
                                        class="text-sm font-medium py-2 rounded-md transition-all duration-200 {{ $booking_mode === '24hours' ? 'bg-white text-gray-900 shadow ring-1 ring-black/5 font-bold' : 'text-gray-500 hover:text-gray-700' }}">
                                        24 Hours
                                        <span class="block text-[10px] font-normal opacity-75">+1 Day</span>
                                    </button>
                                    <button type="button" wire:click="setBookingMode('custom')"
                                        class="text-sm font-medium py-2 rounded-md transition-all duration-200 {{ $booking_mode === 'custom' ? 'bg-white text-gray-900 shadow ring-1 ring-black/5 font-bold' : 'text-gray-500 hover:text-gray-700' }}">
                                        Custom
                                        <span class="block text-[10px] font-normal opacity-75">Manual Time</span>
                                    </button>
                                </div>
                            </div>

                            {{-- DATES & TIMES --}}
                            <div>
                                <label class="block text-xs font-medium text-gray-900 mb-1.5">Tanggal Mulai <span class="text-red-600">*</span></label>
                                <input wire:model.live="date_from" type="date" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-gray-900 focus:outline-none">
                                @error('date_from') <div class="text-xs text-red-600 mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-900 mb-1.5">Tanggal Selesai <span class="text-red-600">*</span></label>
                                {{-- wire:key is crucial for forced updates --}}
                                <input wire:model.live="date_to" wire:key="to-{{ $booking_mode }}-{{ $date_from }}" type="date"
                                    @if($booking_mode !== 'custom') readonly @endif
                                    class="w-full px-3 py-2 text-sm border rounded-md focus:outline-none focus:ring-2 focus:ring-gray-900 {{ $booking_mode !== 'custom' ? 'bg-gray-100 text-gray-500 border-gray-200 cursor-not-allowed' : 'border-gray-300' }}">
                                @error('date_to') <div class="text-xs text-red-600 mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-900 mb-1.5">Jam Mulai <span class="text-red-600">*</span></label>
                                <input wire:model="start_time" wire:key="start-{{ $booking_mode }}" type="time"
                                    @if($booking_mode !== 'custom') readonly @endif
                                    class="w-full px-3 py-2 text-sm border rounded-md focus:outline-none focus:ring-2 focus:ring-gray-900 {{ $booking_mode !== 'custom' ? 'bg-gray-100 text-gray-500 border-gray-200 cursor-not-allowed' : 'border-gray-300' }}">
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-900 mb-1.5">Jam Selesai <span class="text-red-600">*</span></label>
                                <input wire:model="end_time" wire:key="end-{{ $booking_mode }}" type="time"
                                    @if($booking_mode !== 'custom') readonly @endif
                                    class="w-full px-3 py-2 text-sm border rounded-md focus:outline-none focus:ring-2 focus:ring-gray-900 {{ $booking_mode !== 'custom' ? 'bg-gray-100 text-gray-500 border-gray-200 cursor-not-allowed' : 'border-gray-300' }}">
                            </div>

                            {{-- Rest of Form --}}
                            <div class="md:col-span-2">
                                <label class="block text-xs font-medium text-gray-900 mb-1.5">Keperluan <span class="text-red-600">*</span></label>
                                <input wire:model="purpose" type="text" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-gray-900 focus:outline-none">
                            </div>

                            <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-900 mb-1.5">Tujuan Lokasi</label>
                                    <input wire:model="destination" type="text" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-gray-900 focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-900 mb-1.5">Area Ganjil/Genap</label>
                                    <select wire:model="odd_even_area" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-gray-900 focus:outline-none">
                                        <option value="tidak">Tidak Masuk Area</option><option value="ganjil">Ganjil</option><option value="genap">Genap</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-900 mb-1.5">Jenis Keperluan</label>
                                <select wire:model="purpose_type" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-gray-900 focus:outline-none">
                                    <option value="dinas">Dinas (Visitasi)</option><option value="operasional">Operasional (Logistik)</option><option value="antar_jemput">Antar Jemput</option><option value="lainnya">Lainnya</option>
                                </select>
                            </div>

                            {{-- VEHICLE SELECTOR --}}
                            <div>
                                <label class="block text-xs font-medium text-gray-900 mb-1.5">Pilih Kendaraan (Opsional)</label>
                                <select wire:model.live="vehicle_id" @if(!$hasVehicles) disabled @endif 
                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-gray-900 focus:outline-none disabled:bg-gray-100">
                                    <option value="">Sembarang Kendaraan</option>
                                    @foreach($vehicles as $v)
                                        @php
                                            $isUnavailable = in_array($v->vehicle_id, $unavailableVehicleIds);
                                            $label = ($v->vehicle_name ?? $v->name) . ($v->plate_number ? " — " . $v->plate_number : '');
                                        @endphp
                                        <option value="{{ $v->vehicle_id }}" @if($isUnavailable) disabled @endif>
                                            {{ $label }} {{ $isUnavailable ? '(Unavailable)' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Footer --}}
                        <div class="pt-4 border-t border-gray-100">
                            <label class="flex items-center gap-2 cursor-pointer"><input wire:model="has_sim_a" type="checkbox" class="rounded border-gray-300 text-gray-900 focus:ring-gray-900"><span class="text-sm text-gray-700">Saya memiliki SIM A (Wajib)</span></label>
                            <label class="flex items-center gap-2 cursor-pointer mt-2"><input wire:model="agree_terms" type="checkbox" class="rounded border-gray-300 text-gray-900 focus:ring-gray-900"><span class="text-sm text-gray-700">Saya Menyetujui Syarat & Ketentuan</span></label>
                        </div>
                        <div class="flex items-center gap-3 pt-2">
                            <button type="button" wire:click="resetForm" class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50 inline-flex items-center gap-1.5">
                                <x-heroicon-o-arrow-path class="w-4 h-4" />
                                Clear
                            </button>
                            <button type="submit" class="px-4 py-2 bg-gray-900 text-white rounded-lg text-sm hover:bg-gray-800 inline-flex items-center gap-1.5">
                                <x-heroicon-o-check-circle class="w-4 h-4" />
                                Submit Request
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>

        {{-- RIGHT: Sidebar --}}
        <div class="space-y-6">
            {{-- Availability --}}
            <div class="bg-white rounded-xl shadow-sm border-2 border-black p-4 md:p-5" wire:poll.5000ms="loadAvailability">
                <h3 class="text-lg font-semibold text-gray-900 mb-1">Availability</h3>
                <p class="text-xs text-gray-500 mb-3">Live status for selected date/time</p>
                <div class="space-y-3">
                    @forelse($availability as $a)
                        <div class="flex justify-between p-3 border rounded-lg {{ $a['status'] === 'available' ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200' }}">
                            <span class="font-medium text-sm text-gray-900">{{ $a['label'] }}</span>
                            <span class="text-xs font-bold uppercase {{ $a['status'] === 'available' ? 'text-green-700' : 'text-red-700' }}">{{ $a['status'] }}</span>
                        </div>
                    @empty
                        <div class="text-sm text-gray-500 italic">Select valid dates.</div>
                    @endforelse
                </div>
            </div>

            {{-- Recent Bookings --}}
            <div class="bg-white rounded-xl shadow-sm border-2 border-black p-4 md:p-5">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Vehicle Usage</h3>
                <div class="space-y-4">
                    @forelse($recentBookings as $rb)
                        <div class="flex items-start gap-3 pb-3 border-b border-gray-100 last:border-0 last:pb-0">
                            {{-- Car Icon --}}
                            <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center flex-shrink-0 border border-gray-200 text-gray-600">
                                {{-- ORIGINAL SVG: Car/Bus Icon (Transportation) --}}
                                <x-heroicon-o-truck class="h-4 w-4" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2">
                                    <h4 class="font-bold text-sm text-gray-900 truncate">{{ $rb->vehicle->name ?? 'Unknown' }}</h4>
                                    @php
                                        $statusColor = match($rb->status) {
                                            'approved' => 'bg-green-100 text-green-800', 'pending' => 'bg-yellow-100 text-yellow-800',
                                            'returned' => 'bg-indigo-100 text-indigo-800', default => 'bg-gray-100 text-gray-800'
                                        };
                                    @endphp
                                    <span class="shrink-0 text-[10px] font-bold uppercase px-2 py-0.5 rounded {{ $statusColor }}">{{ str_replace('_', ' ', $rb->status) }}</span>
                                </div>
                                <p class="text-xs text-gray-500 mt-1 leading-tight">
                                    Booked by <span class="font-medium text-gray-700">{{ $rb->borrower_name }}</span><br>
                                    <span class="opacity-75">{{ \Carbon\Carbon::parse($rb->start_at)->format('d M, H:i') }}</span>
                                </p>
                            </div>
                        </div>
                    @empty
                        <div class="text-sm text-gray-500 text-center py-2">No recent bookings.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>