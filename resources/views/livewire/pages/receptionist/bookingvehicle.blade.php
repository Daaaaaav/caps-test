<div class="min-h-screen bg-gray-50">
    @php
        $card   = 'bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden';
        $head   = 'bg-gradient-to-r from-gray-900 to-black';
        $hpad   = 'px-6 py-5';
        $label  = 'block text-sm font-medium text-gray-700 mb-2';
        $input  = 'w-full h-10 px-3 rounded-lg border border-gray-300 text-gray-900 placeholder:text-gray-400 focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10 bg-white transition';
        $btnBlk = 'px-3 py-2 text-xs font-medium rounded-lg bg-gray-900 text-white hover:bg-black focus:outline-none focus:ring-2 focus:ring-gray-900/20 disabled:opacity-60 transition';
    @endphp

    <style>
      :root { color-scheme: light; }
      select, option {
        color:#111827 !important;
        background:#ffffff !important;
        -webkit-text-fill-color:#111827 !important;
      }
      option:checked { background:#e5e7eb !important; color:#111827 !important; }
    </style>

    <div class="px-4 sm:px-6 py-6 space-y-8">
        {{-- HERO --}}
        <div class="relative overflow-hidden rounded-2xl {{ $head }} text-white shadow-2xl">
            <div class="pointer-events-none absolute inset-0 opacity-10">
                <div class="absolute top-0 -right-4 w-24 h-24 bg-white rounded-full blur-xl"></div>
                <div class="absolute bottom-0 -left-4 w-16 h-16 bg-white rounded-full blur-lg"></div>
            </div>
            <div class="relative z-10 p-6 sm:p-8">
                <div class="flex items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm border border-white/20">
                            <x-heroicon-o-truck class="w-6 h-6 text-white"/>
                        </div>
                        <div>
                            <h2 class="text-lg sm:text-xl font-semibold">Vehicle Booking (Receptionist)</h2>
                            <p class="text-sm text-white/80">
                                Isi form di bawah untuk mengajukan peminjaman kendaraan atas nama user/departemen tertentu.
                            </p>
                        </div>
                    </div>

                    <div class="hidden md:inline-flex rounded-lg overflow-hidden bg-white/10 border border-white/20 backdrop-blur-sm">
                        <a href="{{ route('bookingstatus') }}"
                           class="px-3 md:px-4 py-2 text-xs md:text-sm font-medium text-white/80 hover:text-white border-r border-white/20 inline-flex items-center gap-2">
                            <x-heroicon-o-calendar-days class="w-4 h-4"/>
                            Room Booking
                        </a>
                        <a href="{{ route('receptionist.vehiclestatus') ?? '#' }}"
                           class="px-3 md:px-4 py-2 text-xs md:text-sm font-medium bg-white text-gray-900 inline-flex items-center gap-2">
                            <x-heroicon-o-truck class="w-4 h-4"/>
                            Vehicle Status
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- FORM --}}
        <div class="{{ $card }}">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center gap-3">
                    <div class="w-2 h-2 bg-gray-900 rounded-full"></div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">Booking Kendaraan</h3>
                        <p class="text-sm text-gray-500">Lengkapi detail peminjaman kendaraan</p>
                    </div>
                </div>
            </div>

            <div class="p-6">
                @if(session()->has('success'))
                    <div class="mb-6 text-sm bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-lg">
                        {{ session('success') }}
                    </div>
                @endif

                <form wire:submit.prevent="submit" class="space-y-6">
                    {{-- ROW: Departemen + User --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        {{-- Departemen --}}
                        <div>
                            <label class="{{ $label }}">Departemen <span class="text-red-600">*</span></label>

                            {{-- search departemen --}}
                            <input
                                type="text"
                                wire:model.live="departmentSearch"
                                placeholder="Cari departemen..."
                                class="{{ $input }} mb-2"
                            >

                            <select wire:model.live="department_id" class="{{ $input }}">
                                <option value="">Pilih departemen</option>
                                @foreach($departments as $d)
                                    <option value="{{ $d->department_id }}">{{ $d->department_name }}</option>
                                @endforeach
                            </select>
                            @error('department_id')
                                <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- User peminjam (filtered by department) --}}
                        <div>
                            <label class="{{ $label }}">User (filtered by department)</label>

                            {{-- search user --}}
                            <input
                                type="text"
                                wire:model.live="userSearch"
                                placeholder="Cari user..."
                                class="{{ $input }} mb-2 @if(!$department_id) bg-gray-100 cursor-not-allowed @endif"
                                @disabled(!$department_id)
                            >

                            <select
                                wire:model.defer="borrower_user_id"
                                class="{{ $input }} @if(!$department_id) bg-gray-100 cursor-not-allowed @endif"
                                @disabled(!$department_id)
                            >
                                @if(!$department_id)
                                    <option value="">Pilih departemen terlebih dahulu</option>
                                @else
                                    <option value="">— Select User —</option>
                                    @forelse($users as $u)
                                        <option value="{{ $u->user_id }}">
                                            {{ $u->full_name }} — {{ $u->email }}
                                        </option>
                                    @empty
                                        <option value="">— No users found —</option>
                                    @endforelse
                                @endif
                            </select>

                            <p class="text-[11px] text-gray-500 mt-1">
                                Jika tidak memilih user, isi nama peminjam manual di kolom di bawah.
                            </p>
                            @error('borrower_user_id')
                                <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Nama peminjam manual --}}
                        <div>
                            <label class="{{ $label }}">
                                Nama Peminjam (manual) <span class="text-red-600">*</span>
                            </label>
                            <input
                                type="text"
                                wire:model.defer="borrower_name"
                                placeholder="Nama peminjam"
                                class="{{ $input }}"
                            >
                            @error('borrower_name')
                                <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Kendaraan --}}
                        <div>
                            <label class="{{ $label }}">Kendaraan <span class="text-red-600">*</span></label>
                            <select
                                wire:model.defer="vehicle_id"
                                @if(!$hasVehicles) disabled @endif
                                class="{{ $input }}"
                            >
                                @if(!$hasVehicles)
                                    <option value="">Data kendaraan belum tersedia</option>
                                @else
                                    <option value="">Pilih kendaraan</option>
                                    @foreach($vehicles as $v)
                                        @php
                                            $vehicleLabel = $v->name ?? 'Kendaraan';
                                            $plate = $v->plate_number ? ' — '.$v->plate_number : '';
                                        @endphp
                                        <option value="{{ $v->vehicle_id }}">
                                            {{ $vehicleLabel }}{{ $plate }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            @error('vehicle_id')
                                <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Jam mulai --}}
                        <div>
                            <label class="{{ $label }}">Pukul Mulai <span class="text-red-600">*</span></label>
                            <input type="time" wire:model.defer="start_time" class="{{ $input }}">
                            @error('start_time')
                                <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Jam selesai --}}
                        <div>
                            <label class="{{ $label }}">Pukul Selesai <span class="text-red-600">*</span></label>
                            <input type="time" wire:model.defer="end_time" class="{{ $input }}">
                            @error('end_time')
                                <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Tanggal pinjam --}}
                        <div>
                            <label class="{{ $label }}">Tanggal Peminjaman <span class="text-red-600">*</span></label>
                            <input type="date" wire:model.defer="date_from" class="{{ $input }}">
                            @error('date_from')
                                <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Tanggal kembali --}}
                        <div>
                            <label class="{{ $label }}">Tanggal Pengembalian <span class="text-red-600">*</span></label>
                            <input type="date" wire:model.defer="date_to" class="{{ $input }}">
                            @error('date_to')
                                <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Keperluan --}}
                    <div>
                        <label class="{{ $label }}">Keperluan <span class="text-red-600">*</span></label>
                        <input
                            type="text"
                            wire:model.defer="purpose"
                            placeholder="Uraian singkat keperluan"
                            class="{{ $input }}"
                        >
                        @error('purpose')
                            <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Tujuan --}}
                    <div>
                        <label class="{{ $label }}">Tujuan Lokasi</label>
                        <input
                            type="text"
                            wire:model.defer="destination"
                            placeholder="Contoh: Kantor Cabang Cibubur"
                            class="{{ $input }}"
                        >
                        @error('destination')
                            <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Odd / Even + Jenis Keperluan --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        {{-- Odd/even --}}
                        <div>
                            <label class="{{ $label }}">Masuk Area Ganjil/Genap</label>
                            <select wire:model.defer="odd_even_area" class="{{ $input }}">
                                <option value="tidak">Tidak Masuk</option>
                                <option value="ganjil">Ganjil</option>
                                <option value="genap">Genap</option>
                            </select>
                            @error('odd_even_area')
                                <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Jenis keperluan --}}
                        <div>
                            <label class="{{ $label }}">Jenis Keperluan</label>
                            <select wire:model.live="purpose_type" class="{{ $input }}">
                                <option value="">Pilih Keperluan</option>
                                <option value="dinas">Dinas</option>
                                <option value="operasional">Operasional</option>
                                <option value="antar jemput">Antar Jemput</option>
                                <option value="lainnya">Lainnya</option>
                            </select>
                            @error('purpose_type')
                                <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Conditional field for "Lainnya" --}}
                    @if($purpose_type === 'lainnya')
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <label class="{{ $label }}">
                                Detail Keperluan Lainnya <span class="text-red-600">*</span>
                            </label>
                            <input
                                type="text"
                                wire:model.defer="purpose_type_other"
                                placeholder="Jelaskan keperluan lainnya secara detail"
                                class="{{ $input }}"
                            >
                            <p class="text-[11px] text-gray-500 mt-1">
                                Wajib diisi karena Anda memilih "Lainnya"
                            </p>
                            @error('purpose_type_other')
                                <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

                    {{-- Terms --}}
                    <div class="pt-2">
                        <label class="inline-flex items-center gap-2 text-sm">
                            <input
                                type="checkbox"
                                wire:model.defer="terms_agreed"
                                class="rounded border-gray-300 text-gray-900 focus:ring-gray-900"
                            >
                            <span>Saya menyetujui syarat & ketentuan peminjaman kendaraan.</span>
                        </label>
                        @error('terms_agreed')
                            <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Submit Button --}}
                    <div class="pt-2">
                        <button type="submit" class="{{ $btnBlk }}">
                            Submit Booking
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
