<div class="bg-gray-50 min-h-screen">
    @php
    $card = 'bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden';
    $label = 'block text-sm font-medium text-gray-700 mb-2';
    $input = 'w-full h-10 px-3 rounded-lg border border-gray-300 text-gray-800 placeholder:text-gray-400 focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10 bg-white transition';
    $btnBlk = 'px-3 py-2 text-xs font-medium rounded-lg bg-gray-900 text-white hover:bg-black focus:outline-none focus:ring-2 focus:ring-gray-900/20 disabled:opacity-60 transition';
    $btnRed = 'px-3 py-2 text-xs font-medium rounded-lg bg-rose-600 text-white hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-600/20 disabled:opacity-60 transition';
    $chip = 'inline-flex items-center gap-2 px-2.5 py-1 rounded-lg bg-gray-100 text-xs text-black';
    $mono = 'text-[10px] font-mono text-gray-500 bg-gray-100 px-2.5 py-1 rounded-md';
    $ico = 'w-10 h-10 bg-gray-900 rounded-xl flex items-center justify-center text-white font-semibold text-sm shrink-0';
    @endphp

    <main class="px-4 sm:px-6 py-6 space-y-8">

        {{-- HERO SECTION --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-gray-900 to-black text-white shadow-2xl">
            <div class="pointer-events-none absolute inset-0 opacity-10">
                <div class="absolute top-0 -right-4 w-24 h-24 bg-white rounded-full blur-xl"></div>
                <div class="absolute bottom-0 -left-4 w-16 h-16 bg-white rounded-full blur-lg"></div>
            </div>
            <div class="relative z-10 p-6 sm:p-8">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm border border-white/20">
                        <x-heroicon-o-bars-3 class="w-6 h-6 text-white" />
                    </div>
                    <div>
                        <h2 class="text-lg sm:text-xl font-semibold">Wifi Management</h2>
                        <p class="text-sm text-white/80">
                            Cabang: <span
                                class="font-semibold">{{ optional(Auth::user()->company)->company_name ?? '-' }}</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- FORM CREATE --}}
        <section class="{{ $card }}">
            <div class="px-5 py-4 border-b border-gray-200">
                <h3 class="text-base font-semibold text-gray-900">Tambah WiFi Baru</h3>
                <p class="text-sm text-gray-500">
                    Informasi WiFi ini akan terlihat oleh karyawan internal.
                </p>
            </div>

            <form class="p-5" wire:submit.prevent="store">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="{{ $label }}">SSID Name</label>
                        <input type="text" class="{{ $input }}" wire:model.defer="ssid" placeholder="Contoh: KRB_GUEST_LOBBY">
                        @error('ssid') <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="{{ $label }}">Password</label>
                        <div class="relative">
                            <input type="text" class="{{ $input }} pr-10" wire:model.defer="password" placeholder="Masukkan password wifi">
                            <x-heroicon-o-lock-closed class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                        </div>
                        @error('password') <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="{{ $label }}">Location (Area)</label>
                        <input type="text" class="{{ $input }}" wire:model.defer="location" placeholder="Contoh: Lobby Utama, Ruang Meeting">
                        @error('location') <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="{{ $label }}">Status</label>
                        <select class="{{ $input }}" wire:model.defer="is_active">
                            <option value="1">Active (Aktif)</option>
                            <option value="0">Inactive (Non-aktif)</option>
                        </select>
                        @error('is_active') <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="pt-5">
                    <button type="submit" wire:loading.attr="disabled" wire:target="store"
                        class="inline-flex items-center gap-2 px-5 h-10 rounded-xl bg-gray-900 text-white text-sm font-medium shadow-sm focus:outline-none focus:ring-2 focus:ring-gray-900/20 transition active:scale-95 hover:bg-black disabled:opacity-60 relative overflow-hidden"
                        wire:loading.class="opacity-80 cursor-wait">
                        <span class="flex items-center gap-2" wire:loading.remove wire:target="store">
                            <x-heroicon-o-check class="w-4 h-4" />
                            Simpan WiFi
                        </span>
                        <span class="flex items-center gap-2" wire:loading wire:target="store">
                            <x-heroicon-o-arrow-path class="h-4 w-4 animate-spin" />
                            Menyimpan...
                        </span>
                    </button>
                </div>
            </form>
        </section>

        {{-- LIST WIFI --}}
        <div class="{{ $card }}">
            <div class="px-5 py-4 border-b border-gray-200 bg-gray-50/50">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-900">Daftar WiFi</h3>
                    <span class="text-xs text-gray-500 bg-white px-2 py-1 rounded border border-gray-200">
                        Total: {{ $wifis->total() }}
                    </span>
                </div>
            </div>

            <div class="divide-y divide-gray-200">
                @forelse ($wifis as $wifi)
                @php
                $rowNo = (($wifis->currentPage() - 1) * $wifis->perPage()) + $loop->iteration;
                @endphp

                <div class="px-5 py-5 hover:bg-gray-50 transition-colors group" wire:key="wifi-{{ $wifi->wifi_id }}">
                    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                        <div class="flex items-start gap-4 flex-1">
                            <div class="{{ $ico }} bg-gray-900 group-hover:scale-105 transition-transform duration-300">
                                <x-heroicon-o-signal class="w-5 h-5 text-white" />
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2 mb-1.5">
                                    <h4 class="font-semibold text-gray-900 text-sm sm:text-base truncate">
                                        {{ $wifi->ssid }}
                                    </h4>

                                    {{-- Status Badge --}}
                                    @if($wifi->is_active)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 text-[10px] font-medium border border-emerald-100">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Active
                                    </span>
                                    @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-gray-100 text-gray-600 text-[10px] font-medium border border-gray-200">
                                        Inactive
                                    </span>
                                    @endif

                                    {{-- Location Badge --}}
                                    @if($wifi->location)
                                    <span class="{{ $chip }} bg-blue-50 text-blue-700 border border-blue-100">
                                        <x-heroicon-o-map-pin class="w-3 h-3" />
                                        {{ $wifi->location }}
                                    </span>
                                    @endif
                                </div>

                                {{-- Password Section --}}
                                <div class="flex items-center gap-2">
                                    <div class="flex items-center gap-2 px-2 py-1 rounded bg-gray-100 border border-gray-200 max-w-fit group/pass">
                                        <x-heroicon-o-key class="w-3.5 h-3.5 text-gray-400" />
                                        <span class="text-xs font-mono text-gray-600 select-all cursor-text">{{ $wifi->password }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-right shrink-0 space-y-3">
                            <div class="{{ $mono }} inline-block">No. {{ $rowNo }}</div>
                            <div class="flex flex-wrap gap-2 justify-end">
                                <button class="{{ $btnBlk }}" wire:click="openEdit({{ $wifi->wifi_id }})"
                                    wire:loading.attr="disabled" wire:target="openEdit({{ $wifi->wifi_id }})">
                                    <span class="inline-flex items-center gap-1.5" wire:loading.remove wire:target="openEdit({{ $wifi->wifi_id }})">
                                        <x-heroicon-o-pencil-square class="w-3.5 h-3.5" />
                                        Edit
                                    </span>
                                    <span class="inline-flex items-center gap-1.5" wire:loading wire:target="openEdit({{ $wifi->wifi_id }})">
                                        <x-heroicon-o-arrow-path class="w-3.5 h-3.5 animate-spin" />
                                        Loading
                                    </span>
                                </button>

                                <button class="{{ $btnRed }}" wire:click="delete({{ $wifi->wifi_id }})"
                                    onclick="confirm('Apakah Anda yakin ingin menghapus WiFi {{ $wifi->ssid }}?') || event.stopImmediatePropagation()"
                                    wire:loading.attr="disabled" wire:target="delete({{ $wifi->wifi_id }})">
                                    <span class="flex items-center gap-1.5">
                                        <x-heroicon-o-trash class="w-3.5 h-3.5" />
                                        Hapus
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="px-5 py-16 text-center">
                    <div class="mx-auto h-16 w-16 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                        <x-heroicon-o-wifi class="w-8 h-8 text-gray-300" />
                    </div>
                    <h3 class="text-sm font-medium text-gray-900">Belum ada data WiFi</h3>
                    <p class="mt-1 text-sm text-gray-500 max-w-sm mx-auto">
                        Mulai tambahkan data WiFi untuk perusahaan ini.
                    </p>
                </div>
                @endforelse
            </div>

            @if($wifis->hasPages())
            <div class="px-5 py-4 bg-gray-50 border-t border-gray-200">
                <div class="flex justify-center">
                    {{ $wifis->links() }}
                </div>
            </div>
            @endif
        </div>

        {{-- MODAL EDIT --}}
        @if($modalEdit)
        <div class="fixed inset-0 z-[60] flex items-center justify-center" role="dialog" aria-modal="true"
            wire:key="modal-edit" wire:keydown.escape.window="closeEdit">

            <button type="button" class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity" aria-label="Close overlay" wire:click="closeEdit"></button>

            <div class="relative w-full max-w-2xl mx-4 {{ $card }} shadow-2xl transform transition-all focus:outline-none z-10" tabindex="-1">
                <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between bg-gray-50/50">
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">Edit WiFi</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Perbarui informasi jaringan WiFi.</p>
                    </div>
                    <button class="text-gray-400 hover:text-gray-700 transition p-1 rounded-md hover:bg-gray-100" type="button" wire:click="closeEdit" aria-label="Close">
                        <x-heroicon-o-x-mark class="w-5 h-5" />
                    </button>
                </div>

                <form class="p-6" wire:submit.prevent="update">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="{{ $label }}">SSID Name</label>
                            <input type="text" class="{{ $input }}" wire:model.defer="edit_ssid">
                            @error('edit_ssid') <p class="mt-1.5 text-xs text-rose-600 font-medium flex items-center gap-1"><x-heroicon-s-exclamation-circle class="w-3 h-3" /> {{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="{{ $label }}">Password</label>
                            <div class="relative">
                                <input type="text" class="{{ $input }} pr-10" wire:model.defer="edit_password">
                                <x-heroicon-o-key class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                            </div>
                            @error('edit_password') <p class="mt-1.5 text-xs text-rose-600 font-medium flex items-center gap-1"><x-heroicon-s-exclamation-circle class="w-3 h-3" /> {{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="{{ $label }}">Location</label>
                            <input type="text" class="{{ $input }}" wire:model.defer="edit_location">
                            @error('edit_location') <p class="mt-1.5 text-xs text-rose-600 font-medium flex items-center gap-1"><x-heroicon-s-exclamation-circle class="w-3 h-3" /> {{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="{{ $label }}">Status</label>
                            <select class="{{ $input }}" wire:model.defer="edit_is_active">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                            @error('edit_is_active') <p class="mt-1.5 text-xs text-rose-600 font-medium flex items-center gap-1"><x-heroicon-s-exclamation-circle class="w-3 h-3" /> {{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="mt-8 flex items-center justify-end gap-3 border-t border-gray-100 pt-5 -mx-6 -mb-6 bg-gray-50/50 px-6 pb-6 rounded-b-2xl">
                        <button type="button"
                            class="px-4 h-10 rounded-xl border border-gray-300 text-gray-700 text-sm font-medium hover:bg-white hover:border-gray-400 focus:ring-2 focus:ring-gray-200 transition shadow-sm bg-white"
                            wire:click="closeEdit">
                            Batal
                        </button>
                        <button type="submit" wire:loading.attr="disabled" wire:target="update"
                            class="inline-flex items-center gap-2 px-5 h-10 rounded-xl bg-gray-900 text-white text-sm font-medium shadow-sm hover:bg-black focus:outline-none focus:ring-2 focus:ring-gray-900/20 transition disabled:opacity-60">
                            <span class="flex items-center gap-2" wire:loading.remove wire:target="update">
                                <x-heroicon-o-check class="w-4 h-4" />
                                Simpan Perubahan
                            </span>
                            <span class="flex items-center gap-2" wire:loading wire:target="update">
                                <x-heroicon-o-arrow-path class="h-4 w-4 animate-spin" />
                                Menyimpan...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endif
    </main>
</div>