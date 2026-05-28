<div class="min-h-screen bg-gray-50">
    @php
        $card   = 'bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden';
        $head   = 'bg-[#4A2F24]';
        $label  = 'block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1.5';
        $input  = 'w-full h-10 px-3.5 rounded-lg border border-gray-300 bg-white text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-900/10 focus:border-gray-900 transition-all';
        $btnBlk = 'inline-flex items-center justify-center gap-2 px-5 h-10 text-xs font-semibold rounded-lg bg-[#4E653D] text-white hover:bg-[#354C2B] transition shadow-sm focus:outline-none focus:ring-2 focus:ring-[#4E653D]/20 disabled:opacity-60';
    @endphp

    <style>
      :root { color-scheme: light; }
      select, option {
        color: #111827 !important;
        background: #ffffff !important;
      }
      option:checked { background: #f3f4f6 !important; color: #111827 !important; }
    </style>

    <main class="px-4 sm:px-6 py-6 space-y-6">

        {{-- Hero Header --}}
        <div class="relative overflow-hidden rounded-2xl {{ $head }} text-[#CDDEA7] shadow-2xl">
            <div class="pointer-events-none absolute inset-0 opacity-10">
                <div class="absolute top-0 -right-4 w-24 h-24 bg-[#CDDEA7] rounded-full blur-xl"></div>
                <div class="absolute bottom-0 -left-4 w-16 h-16 bg-[#CDDEA7] rounded-full blur-lg"></div>
            </div>
            <div class="relative z-10 p-6 sm:p-8">
                <div class="flex items-center justify-between gap-4 flex-wrap">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-[#CDDEA7]/10 rounded-xl flex items-center justify-center backdrop-blur-sm border border-[#CDDEA7]/20">
                            <x-heroicon-o-book-open class="w-6 h-6 text-[#CDDEA7]" />
                        </div>
                        <div>
                            <h2 class="text-lg sm:text-xl font-semibold">{{ __('app.guestbook_title') }}</h2>
                            <p class="text-xs text-[#CDDEA7]/80">{{ __('app.guestbook_subtitle') }}</p>
                        </div>
                    </div>

                    <div class="inline-flex rounded-lg overflow-hidden bg-[#CDDEA7]/10 border border-[#CDDEA7]/20 backdrop-blur-sm shadow-sm">
                        <a href="{{ route('receptionist.guestbookhistory') }}"
                           class="px-4 py-2 text-xs font-bold bg-[#CDDEA7] text-[#4A2F24] hover:bg-[#CDDEA7]/90 inline-flex items-center gap-1.5 transition">
                            <x-heroicon-o-calendar class="w-4 h-4"/>
                            <span>{{ __('app.guestbook_history') }}</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Form Entri Baru --}}
        <div class="{{ $card }}">
            <div class="px-6 py-5 border-b border-gray-200 bg-gray-50/50 flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-[#4E653D]/10 flex items-center justify-center border border-[#4E653D]/20">
                    <x-heroicon-o-plus class="w-4.5 h-4.5 text-[#4E653D]" />
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-900">Tambah Entri Baru</h3>
                    <p class="text-xs text-gray-500 mt-0.5 font-medium">Lengkapi data kunjungan tamu hari ini.</p>
                </div>
            </div>

            <form wire:submit.prevent="save" class="p-6 space-y-6">
                {{-- Auto-recorded fields badge row --}}
                <div class="flex flex-wrap items-center gap-2.5 text-xs">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-[#4E653D]/10 border border-[#4E653D]/25 text-[#4E653D] font-semibold shadow-sm">
                        <x-heroicon-o-calendar class="w-3.5 h-3.5" />
                        Tanggal otomatis
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-[#4E653D]/10 border border-[#4E653D]/25 text-[#4E653D] font-semibold shadow-sm">
                        <x-heroicon-o-clock class="w-3.5 h-3.5" />
                        Jam masuk otomatis
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-gray-100 border border-gray-200 text-gray-600 font-semibold shadow-sm">
                        <x-heroicon-o-user class="w-3.5 h-3.5" />
                        Petugas: {{ auth()->user()->full_name ?? auth()->user()->name ?? 'Receptionist' }}
                    </span>
                </div>

                {{-- Grid Form Tamu --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div>
                        <label class="{{ $label }}">Nama Lengkap <span class="text-rose-500">*</span></label>
                        <input type="text" wire:model.defer="name" placeholder="Masukkan nama lengkap" class="{{ $input }}">
                        @error('name') <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="{{ $label }}">Nomor HP</label>
                        <input type="text" wire:model.defer="phone_number" placeholder="08xxxxxxxxxx" class="{{ $input }}">
                        @error('phone_number') <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="{{ $label }}">Nama Instansi</label>
                        <input type="text" wire:model.defer="instansi" placeholder="Nama instansi/perusahaan" class="{{ $input }}">
                        @error('instansi') <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="{{ $label }}">Keperluan <span class="text-rose-500">*</span></label>
                        <input type="text" wire:model.defer="keperluan" placeholder="Tujuan kunjungan" class="{{ $input }}">
                        @error('keperluan') <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p> @enderror
                    </div>

                    {{-- Departemen yang Dituju --}}
                    <div>
                        <label class="{{ $label }}">
                            Departemen yang Dituju <span class="text-gray-400 font-normal">(Opsional)</span>
                        </label>
                        <div class="relative">
                            <select wire:model.live="department_id" class="{{ $input }} appearance-none pr-8">
                                <option value="">-- Pilih Departemen --</option>
                                @foreach($departments_list as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name ?? $dept->nama_departemen ?? 'Dept #'.$dept->id }}</option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-gray-500">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                            </div>
                        </div>
                        @error('department_id') <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p> @enderror
                    </div>

                    {{-- Bertemu dengan --}}
                    <div>
                        <label class="{{ $label }}">
                            Bertemu dengan <span class="text-gray-400 font-normal">(Opsional)</span>
                        </label>
                        <div class="relative">
                            <select wire:model.defer="user_id"
                                    class="{{ $input }} appearance-none pr-8 disabled:bg-gray-100 disabled:text-gray-400"
                                    @if(empty($users_list) && $department_id) disabled @endif>
                                <option value="">-- Pilih Pegawai --</option>
                                @foreach($users_list as $user)
                                    <option value="{{ $user->id }}">{{ $user->full_name ?? $user->name }}</option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-gray-500">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                            </div>
                        </div>
                        @if(empty($users_list) && $department_id)
                            <p class="mt-1.5 text-xs text-amber-600 font-semibold">Tidak ada user di departemen ini.</p>
                        @endif
                        @error('user_id') <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Submit Button --}}
                <div class="pt-5 border-t border-gray-200 bg-gray-50/50 -mx-6 -mb-6 p-6 flex items-center justify-end gap-3">
                    @if (session('saved'))
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/25 text-emerald-600 text-xs font-bold shadow-sm">
                            <x-heroicon-o-check class="w-3.5 h-3.5 font-bold" />
                            <span>Data Tersimpan!</span>
                        </span>
                    @endif

                    <button type="submit" wire:loading.attr="disabled" wire:target="save" class="{{ $btnBlk }}">
                        <span class="flex items-center gap-2" wire:loading.remove wire:target="save">
                            <x-heroicon-o-check class="w-4 h-4" />
                            <span>Simpan Data</span>
                        </span>
                        <span class="flex items-center gap-2 animate-pulse" wire:loading wire:target="save">
                            <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Menyimpan…</span>
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </main>
</div>