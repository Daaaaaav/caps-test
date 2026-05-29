<div class="min-h-screen bg-background">
    @php
        $card   = 'bg-card border border-border rounded-2xl shadow-xl overflow-hidden';
        $head   = 'bg-[#4A2F24]';
        $hpad   = 'px-6 py-5';
        $label  = 'block text-xs font-semibold uppercase tracking-wider text-muted-foreground mb-1.5';
        $input  = 'w-full h-10 px-3.5 rounded-lg border border-input bg-background text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all';
        $btnBlk = 'inline-flex items-center justify-center gap-2 px-5 h-10 text-xs font-semibold rounded-lg bg-primary text-primary-foreground hover:bg-primary/95 transition shadow-sm focus:outline-none focus:ring-2 focus:ring-primary/20 disabled:opacity-60';
    @endphp

    <style>
      :root { color-scheme: light; }
      select, option {
        color: var(--foreground) !important;
        background: var(--background) !important;
      }
      option:checked { background: var(--muted) !important; color: var(--foreground) !important; }
    </style>

    <div class="px-4 sm:px-6 py-6 space-y-6">
        {{-- HERO --}}
        <div class="relative overflow-hidden rounded-2xl {{ $head }} text-[#CDDEA7] shadow-2xl">
            <div class="pointer-events-none absolute inset-0 opacity-10">
                <div class="absolute top-0 -right-4 w-24 h-24 bg-[#CDDEA7] rounded-full blur-xl"></div>
                <div class="absolute bottom-0 -left-4 w-16 h-16 bg-[#CDDEA7] rounded-full blur-lg"></div>
            </div>
            <div class="relative z-10 p-6 sm:p-8">
                <div class="flex items-center justify-between gap-4 flex-wrap">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-[#CDDEA7]/10 rounded-xl flex items-center justify-center backdrop-blur-sm border border-[#CDDEA7]/20">
                            <x-heroicon-o-truck class="w-6 h-6 text-[#CDDEA7]"/>
                        </div>
                        <div>
                            <h2 class="text-lg sm:text-xl font-semibold">{{ __('app.vehicle_booking_title') }}</h2>
                            <p class="text-xs text-[#CDDEA7]/80 max-w-xl">
                                {{ __('app.vehicle_booking_subtitle') }}
                            </p>
                        </div>
                    </div>

                    <div class="inline-flex rounded-lg overflow-hidden bg-[#CDDEA7]/10 border border-[#CDDEA7]/20 backdrop-blur-sm">
                        <a href="{{ route('receptionist.roomapproval') }}"
                           class="px-3.5 py-2 text-xs font-semibold text-[#CDDEA7]/80 hover:text-[#CDDEA7] hover:bg-[#CDDEA7]/5 border-r border-[#CDDEA7]/20 inline-flex items-center gap-1.5 transition">
                            <x-heroicon-o-calendar-days class="w-4 h-4"/>
                            <span>{{ __('app.booking_room') }}</span>
                        </a>
                        <a href="{{ route('receptionist.vehiclestatus') }}"
                           class="px-3.5 py-2 text-xs font-semibold bg-[#CDDEA7] text-[#4A2F24] hover:bg-[#CDDEA7]/90 inline-flex items-center gap-1.5 transition">
                            <x-heroicon-o-truck class="w-4 h-4"/>
                            <span>{{ __('app.vehicle_status_menu') }}</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- FORM --}}
        <div class="{{ $card }}">
            <div class="px-6 py-5 border-b border-border bg-muted/10 flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-primary/10 flex items-center justify-center">
                    <x-heroicon-o-truck class="w-4.5 h-4.5 text-primary" />
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-foreground">Booking Kendaraan</h3>
                    <p class="text-xs text-muted-foreground mt-0.5">Lengkapi detail peminjaman kendaraan</p>
                </div>
            </div>

            <div class="p-6">
                @if(session()->has('success'))
                    <div class="mb-6 inline-flex items-center gap-2.5 px-4 py-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-500 text-xs font-semibold shadow-sm w-full">
                        <svg class="w-4 h-4 shrink-0 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                <form wire:submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        {{-- Departemen --}}
                        <div>
                            <label class="{{ $label }}">Departemen <span class="text-destructive">*</span></label>

                            <input
                                type="text"
                                wire:model.live="departmentSearch"
                                placeholder="Cari departemen..."
                                class="{{ $input }} mb-2.5"
                            >

                            <div class="relative">
                                <select wire:model.live="department_id" class="{{ $input }} appearance-none pr-8">
                                    <option value="">Pilih departemen</option>
                                    @foreach($departments as $d)
                                        <option value="{{ $d->department_id }}">{{ $d->department_name }}</option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-muted-foreground/60">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                                </div>
                            </div>
                            @error('department_id')
                                <p class="mt-1.5 text-xs text-destructive font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- User peminjam (filtered by department) --}}
                        <div>
                            <label class="{{ $label }}">User (filtered by department)</label>

                            <input
                                type="text"
                                wire:model.live="userSearch"
                                placeholder="Cari user..."
                                class="{{ $input }} mb-2.5 disabled:bg-muted disabled:text-muted-foreground"
                                @disabled(!$department_id)
                            >

                            <div class="relative">
                                <select
                                    wire:model.defer="borrower_user_id"
                                    class="{{ $input }} appearance-none pr-8 disabled:bg-muted disabled:text-muted-foreground"
                                    @disabled(!$department_id)
                                >
                                    @if(!$department_id)
                                        <option value="">Pilih departemen terlebih dahulu</option>
                                    @else
                                        <option value="">{{ __('app.select_user') }}</option>
                                        @forelse($users as $u)
                                            <option value="{{ $u->user_id }}">
                                                {{ $u->full_name }} — {{ $u->email }}
                                            </option>
                                        @empty
                                            <option value="">{{ __('app.no_users_found') }}</option>
                                        @endforelse
                                    @endif
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-muted-foreground/60">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                                </div>
                            </div>

                            <p class="text-[11px] text-muted-foreground mt-1.5">
                                Jika tidak memilih user, isi nama peminjam manual di kolom di bawah.
                            </p>
                            @error('borrower_user_id')
                                <p class="mt-1.5 text-xs text-destructive font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Nama peminjam manual --}}
                        <div>
                            <label class="{{ $label }}">
                                Nama Peminjam (manual) <span class="text-destructive">*</span>
                            </label>
                            <input
                                type="text"
                                wire:model.defer="borrower_name"
                                placeholder="Nama peminjam"
                                class="{{ $input }} md:mt-[50px]"
                            >
                            @error('borrower_name')
                                <p class="mt-1.5 text-xs text-destructive font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Kendaraan --}}
                        <div>
                            <label class="{{ $label }}">Kendaraan <span class="text-destructive">*</span></label>
                            <div class="relative">
                                <select
                                    wire:model.defer="vehicle_id"
                                    @if(!$hasVehicles) disabled @endif
                                    class="{{ $input }} appearance-none pr-8 disabled:bg-muted disabled:text-muted-foreground"
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
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-muted-foreground/60">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                                </div>
                            </div>
                            @error('vehicle_id')
                                <p class="mt-1.5 text-xs text-destructive font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Tanggal pinjam --}}
                        <div>
                            <label class="{{ $label }}">Tanggal Peminjaman <span class="text-destructive">*</span></label>
                            <input type="date" wire:model.defer="date_from" class="{{ $input }}">
                            @error('date_from')
                                <p class="mt-1.5 text-xs text-destructive font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Tanggal kembali --}}
                        <div>
                            <label class="{{ $label }}">Tanggal Pengembalian <span class="text-destructive">*</span></label>
                            <input type="date" wire:model.defer="date_to" class="{{ $input }}">
                            @error('date_to')
                                <p class="mt-1.5 text-xs text-destructive font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Jam mulai --}}
                        <div>
                            <label class="{{ $label }}">Pukul Mulai <span class="text-destructive">*</span></label>
                            <input type="time" wire:model.defer="start_time" class="{{ $input }}">
                            @error('start_time')
                                <p class="mt-1.5 text-xs text-destructive font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Jam selesai --}}
                        <div>
                            <label class="{{ $label }}">Pukul Selesai <span class="text-destructive">*</span></label>
                            <input type="time" wire:model.defer="end_time" class="{{ $input }}">
                            @error('end_time')
                                <p class="mt-1.5 text-xs text-destructive font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Odd/even --}}
                        <div>
                            <label class="{{ $label }}">Masuk Area Ganjil/Genap</label>
                            <div class="relative">
                                <select wire:model.defer="odd_even_area" class="{{ $input }} appearance-none pr-8">
                                    <option value="tidak">Tidak Masuk</option>
                                    <option value="ganjil">Ganjil</option>
                                    <option value="genap">Genap</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-muted-foreground/60">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                                </div>
                            </div>
                            @error('odd_even_area')
                                <p class="mt-1.5 text-xs text-destructive font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Jenis keperluan --}}
                        <div>
                            <label class="{{ $label }}">Jenis Keperluan</label>
                            <div class="relative">
                                <select wire:model.live="purpose_type" class="{{ $input }} appearance-none pr-8">
                                    <option value="">Pilih Keperluan</option>
                                    <option value="dinas">Dinas</option>
                                    <option value="operasional">Operasional</option>
                                    <option value="antar jemput">Antar Jemput</option>
                                    <option value="lainnya">Lainnya</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-muted-foreground/60">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                                </div>
                            </div>
                            @error('purpose_type')
                                <p class="mt-1.5 text-xs text-destructive font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Keperluan --}}
                        <div class="md:col-span-2">
                            <label class="{{ $label }}">Keperluan <span class="text-destructive">*</span></label>
                            <input
                                type="text"
                                wire:model.defer="purpose"
                                placeholder="Uraian singkat keperluan"
                                class="{{ $input }}"
                            >
                            @error('purpose')
                                <p class="mt-1.5 text-xs text-destructive font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Tujuan --}}
                        <div class="md:col-span-3">
                            <label class="{{ $label }}">Tujuan Lokasi</label>
                            <input
                                type="text"
                                wire:model.defer="destination"
                                placeholder="Contoh: Kantor Cabang Cibubur"
                                class="{{ $input }}"
                            >
                            @error('destination')
                                <p class="mt-1.5 text-xs text-destructive font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Conditional field for "Lainnya" --}}
                        @if($purpose_type === 'lainnya')
                            <div class="bg-primary/5 border border-primary/20 rounded-2xl p-5 md:col-span-3">
                                <label class="{{ $label }}">
                                    Detail Keperluan Lainnya <span class="text-destructive">*</span>
                                </label>
                                <input
                                    type="text"
                                    wire:model.defer="purpose_type_other"
                                    placeholder="Jelaskan keperluan lainnya secara detail"
                                    class="{{ $input }}"
                                >
                                <p class="text-[11px] text-muted-foreground mt-1.5 font-medium">
                                    Wajib diisi karena Anda memilih "Lainnya"
                                </p>
                                @error('purpose_type_other')
                                    <p class="mt-1.5 text-xs text-destructive font-medium">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif

                        {{-- Terms --}}
                        <div class="pt-2 md:col-span-3">
                            <label class="inline-flex items-center gap-2.5 cursor-pointer group">
                                <input
                                    type="checkbox"
                                    wire:model.defer="terms_agreed"
                                    class="w-4 h-4 rounded border-input text-primary focus:ring-primary/20 bg-background transition-all"
                                >
                                <span class="text-xs text-muted-foreground font-semibold group-hover:text-primary transition-colors">Saya menyetujui syarat & ketentuan peminjaman kendaraan.</span>
                            </label>
                            @error('terms_agreed')
                                <p class="mt-1.5 text-xs text-destructive font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Submit Button --}}
                    <div class="pt-4 border-t border-border bg-muted/5 -mx-6 -mb-6 p-6 flex items-center justify-end">
                        <button type="submit" class="{{ $btnBlk }}">
                            <x-heroicon-o-check class="w-4 h-4" />
                            <span>{{ __('app.submit_booking') }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
