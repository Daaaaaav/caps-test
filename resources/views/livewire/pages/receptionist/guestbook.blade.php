<div class="min-h-screen bg-gray-50" wire:poll.1000ms>
    @php
        $card = 'bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden';
        $head = 'bg-gradient-to-r from-gray-900 to-black';
        $hpad = 'px-6 py-5';
        $tag = 'w-1.5 bg-white rounded-full';
        $label = 'block text-sm font-medium text-gray-700 mb-2';
        $input = 'w-full h-10 px-3 rounded-lg border border-gray-300 text-gray-800 placeholder:text-gray-400 focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10 bg-white transition';
        $select = 'w-full h-10 px-3 rounded-lg border border-gray-300 text-gray-800 focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10 bg-white transition disabled:bg-gray-100 disabled:text-gray-400';
        $btnBlk = 'px-3 py-2 text-xs font-medium rounded-lg bg-gray-900 text-white hover:bg-black focus:outline-none focus:ring-2 focus:ring-gray-900/20 disabled:opacity-60 transition';
        $btnGrn = 'px-3 py-2 text-xs font-medium rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-600/20 disabled:opacity-60 transition';
        $chip = 'inline-flex items-center gap-2 px-2.5 py-1 rounded-lg bg-gray-100 text-xs';
        $mono = 'text-[10px] font-mono text-gray-500 bg-gray-100 px-2.5 py-1 rounded-md';
        $icoAvatar = 'w-10 h-10 bg-gray-900 rounded-xl flex items-center justify-center text-white font-semibold text-sm shrink-0';
        $icoDot = 'h-6';
        $sectPad = 'px-6 py-5';
    @endphp

    <div class="px-4 sm:px-6 py-6 space-y-8">
        {{-- HERO --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-gray-900 to-black text-white shadow-2xl">
            <div class="pointer-events-none absolute inset-0 opacity-10">
                <div class="absolute top-0 -right-4 w-24 h-24 bg-white rounded-full blur-xl"></div>
                <div class="absolute bottom-0 -left-4 w-16 h-16 bg-white rounded-full blur-lg"></div>
            </div>
            <div class="relative z-10 p-6 sm:p-8">
                <div class="flex items-center gap-4">
                    <div
                        class="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm border border-white/20">
                        <x-heroicon-o-user-group class="w-6 h-6 text-white" />
                    </div>
                    <div>
                        <h2 class="text-lg sm:text-xl font-semibold">Guestbook</h2>
                        <p class="text-sm text-white/80">Kelola kunjungan dan entri tamu hari ini</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Form Entri Baru --}}
        <div class="{{ $card }}">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center gap-3">
                    <div class="w-2 h-2 bg-gray-900 rounded-full"></div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">Tambah Entri Baru</h3>
                        <p class="text-sm text-gray-500">Lengkapi data kunjungan hari ini</p>
                    </div>
                </div>
            </div>

            <form wire:submit.prevent="save" class="p-6 space-y-5">
                {{-- INFO: Tanggal, Jam, & Petugas otomatis --}}
                <div class="p-4 rounded-xl bg-gray-50 border border-dashed border-gray-300 text-sm text-gray-600 flex items-start gap-3">
                    <div class="mt-0.5">
                        <x-heroicon-o-information-circle class="w-5 h-5 text-gray-400" />
                    </div>
                    <div>
                        <p class="font-medium text-gray-800">Tanggal, jam, dan petugas dicatat otomatis.</p>
                        <p class="mt-1">
                            Hari/tanggal &amp; jam masuk akan otomatis sesuai waktu saat tombol
                            <span class="font-semibold text-gray-900">"Simpan Data"</span> ditekan.
                        </p>
                        <p class="mt-1">
                            Petugas penjaga akan tercatat sebagai
                            <span class="font-semibold text-gray-900">
                                {{ auth()->user()->full_name ?? auth()->user()->name ?? 'Petugas Receptionist' }}
                            </span>.
                        </p>
                    </div>
                </div>

                {{-- Data Tamu --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="{{ $label }}">Nama Lengkap</label>
                        <input type="text" wire:model.defer="name" placeholder="Masukkan nama lengkap"
                               class="{{ $input }}">
                        @error('name') <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="{{ $label }}">Nomor HP</label>
                        <input type="text" wire:model.defer="phone_number" placeholder="08xxxxxxxxxx"
                               class="{{ $input }}">
                        @error('phone_number') <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="{{ $label }}">Nama Instansi</label>
                        <input type="text" wire:model.defer="instansi" placeholder="Nama instansi/perusahaan"
                               class="{{ $input }}">
                        @error('instansi') <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="{{ $label }}">Keperluan</label>
                        <input type="text" wire:model.defer="keperluan" placeholder="Tujuan kunjungan"
                               class="{{ $input }}">
                        @error('keperluan') <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Bertemu Dengan Siapa (Optional) --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 pt-2">
                    <div>
                        <label class="{{ $label }}">Departemen yang Dituju <span class="text-gray-400 font-normal">(Opsional)</span></label>
                        {{-- Gunakan wire:model.live agar user list terupdate otomatis --}}
                        <select wire:model.live="department_id" class="{{ $select }}">
                            <option value="">-- Pilih Departemen --</option>
                            @foreach($departments_list as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name ?? $dept->nama_departemen ?? 'Dept #'.$dept->id }}</option>
                            @endforeach
                        </select>
                        @error('department_id') <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p> @enderror
                    </div>
                    
                    <div>
                        <label class="{{ $label }}">Bertemu dengan <span class="text-gray-400 font-normal">(Opsional)</span></label>
                        <select wire:model.defer="user_id" class="{{ $select }}" @if(empty($users_list) && $department_id) disabled @endif>
                            <option value="">-- Pilih Pegawai --</option>
                            @foreach($users_list as $user)
                                <option value="{{ $user->id }}">{{ $user->full_name ?? $user->name }}</option>
                            @endforeach
                        </select>
                        {{-- This message confirms if the user list is empty --}}
                        @if(empty($users_list) && $department_id)
                            <p class="mt-1 text-[10px] text-orange-500">Tidak ada user di departemen ini.</p>
                        @endif
                        @error('user_id') <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="pt-4 flex items-center gap-3 border-t border-gray-100 mt-2">
                    <button type="submit" wire:loading.attr="disabled" wire:target="save" aria-busy="true"
                            class="inline-flex items-center gap-2 px-4 h-10 rounded-lg bg-gray-900 text-white text-sm font-medium
                                   shadow-sm focus:outline-none focus:ring-2 focus:ring-gray-900/20
                                   transition active:scale-95 hover:bg-black disabled:opacity-60 relative overflow-hidden">
                        <span class="flex items-center gap-2" wire:loading.remove wire:target="save">
                            <x-heroicon-o-check class="w-4 h-4" />
                            Simpan Data
                        </span>

                        <span class="flex items-center gap-2" wire:loading wire:target="save">
                            <x-heroicon-o-arrow-path class="animate-spin h-4 w-4" />
                            Menyimpan…
                        </span>
                    </button>

                    @if (session('saved'))
                        <span
                            class="inline-flex items-center gap-1.5 px-3 h-8 rounded-lg bg-emerald-100 text-emerald-700 text-xs font-medium">
                            <x-heroicon-o-check class="w-3.5 h-3.5" />
                            Tersimpan!
                        </span>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>