<div class="bg-gray-50 text-gray-900">
    @php
    $card = 'bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden';
    $label = 'block text-sm font-medium text-gray-700 mb-2';
    $input = 'w-full h-10 px-3 rounded-lg border border-gray-300 text-gray-800 placeholder:text-gray-400 focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10 bg-white transition';
    $btnBlk = 'px-3 py-2 text-xs font-medium rounded-lg bg-gray-900 text-white hover:bg-black focus:outline-none focus:ring-2 focus:ring-gray-900/20 disabled:opacity-60 transition';
    $btnRed = 'px-3 py-2 text-xs font-medium rounded-lg bg-rose-600 text-white hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-600/20 disabled:opacity-60 transition';
    $chip = 'inline-flex items-center gap-2 px-2.5 py-1 rounded-lg bg-gray-100 text-xs';
    $mono = 'text-[10px] font-mono text-gray-500 bg-gray-100 px-2.5 py-1 rounded-md';
    $ico = 'w-10 h-10 bg-gray-900 rounded-xl flex items-center justify-center text-white font-semibold text-sm shrink-0';
    @endphp

    <main class="px-4 sm:px-6 py-6 space-y-8">
        {{-- HERO --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-gray-900 to-black text-white shadow-2xl">
            <div class="pointer-events-none absolute inset-0 opacity-10">
                <div class="absolute top-0 -right-4 w-24 h-24 bg-white rounded-full blur-xl"></div>
                <div class="absolute bottom-0 -left-4 w-16 h-16 bg-white rounded-full blur-lg"></div>
            </div>
            <div class="relative z-10 p-6 sm:p-8">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm border border-white/20">
                        <x-heroicon-o-user-group class="w-6 h-6 text-white" />
                    </div>
                    <div>
                        <h2 class="text-lg sm:text-xl font-semibold">Admin Management</h2>
                        <p class="text-sm text-white/80">
                            Cabang: <span class="font-semibold">{{ $company_name }}</span>
                        </p>
                    </div>
                    <div class="ml-auto flex items-center gap-2">
                        <input type="text" wire:model.live.debounce.400ms="search" placeholder="Search admin..."
                            class="h-10 px-3 rounded-lg border border-white/20 bg-white/10 text-white placeholder:text-white/70 focus:bg-white/20">
                        @if($mode === 'index')
                        <button class="px-3 py-2 text-sm font-medium rounded-lg bg-white text-gray-900 hover:bg-gray-100"
                            wire:click="create">+ New Admin</button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- LIST USERS --}}
            <div class="lg:col-span-2 {{ $card }}">
                <div class="px-5 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-base font-semibold text-gray-900">List Admins</h3>
                        <div class="text-xs text-gray-500">Role: Admin only</div>
                    </div>
                </div>

                <div class="divide-y divide-gray-200">
                    @forelse ($rows as $u)
                    @php $rowNo = (($rows->firstItem() ?? 1) - 0) + $loop->index; @endphp
                    <div class="px-5 py-5 hover:bg-gray-50 transition-colors" wire:key="admin-{{ $u->user_id }}">
                        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                            <div class="flex items-start gap-3 flex-1">
                                <div class="{{ $ico }}">{{ strtoupper(substr($u->full_name, 0, 1)) }}</div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <h4 class="font-semibold text-gray-900 text-sm sm:text-base truncate">
                                            {{ $u->full_name }}
                                        </h4>
                                        <span class="{{ $chip }}"><span class="font-medium text-gray-700">Admin</span></span>
                                        @if($u->department)
                                        <span class="{{ $chip }}">Primary: {{ $u->department->department_name }}</span>
                                        @endif
                                        @foreach($u->departments as $d)
                                        @if(!$u->department || $d->department_id !== $u->department->department_id)
                                        <span class="{{ $chip }}">{{ $d->department_name }}</span>
                                        @endif
                                        @endforeach
                                    </div>
                                    <p class="text-[12px] text-gray-500 truncate">{{ $u->email }}</p>
                                    @if($u->phone_number)
                                    <p class="text-[12px] text-gray-500 mt-0.5">{{ $u->phone_number }}</p>
                                    @endif
                                </div>
                            </div>
                            <div class="text-right shrink-0 space-y-2">
                                <div class="{{ $mono }}">No. {{ $rowNo }}</div>
                                <div class="flex flex-wrap gap-2 justify-end pt-1">
                                    <button class="{{ $btnBlk }}" wire:click="edit({{ $u->user_id }})">Edit</button>
                                    <button class="{{ $btnRed }}" wire:click="destroy({{ $u->user_id }})"
                                        onclick="return confirm('Hapus admin ini?')">Hapus</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="px-5 py-14 text-center text-gray-500 text-sm">Tidak ada admin.</div>
                    @endforelse
                </div>

                @if($rows->hasPages())
                <div class="px-5 py-4 bg-gray-50 border-t border-gray-200">
                    <div class="flex justify-center">
                        {{ $rows->links() }}
                    </div>
                </div>
                @endif
            </div>

            {{-- CREATE form --}}
            <section class="{{ $card }}">
                <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-base font-semibold text-gray-900">
                        Tambah Admin
                    </h3>
                </div>

                <form class="p-5" wire:submit.prevent="store">
                    <div class="grid grid-cols-1 gap-5">
                        <div>
                            <label class="{{ $label }}">Company</label>
                            <input type="text" class="{{ $input }}" value="{{ $company_name }}" readonly>
                        </div>

                        <div>
                            <label class="{{ $label }}">Primary Department</label>
                            <select class="{{ $input }}" wire:model.live="primary_department_id">
                                <option value="">— Choose primary —</option>
                                @foreach($departments as $d)
                                <option value="{{ $d['department_id'] }}">{{ $d['department_name'] }}</option>
                                @endforeach
                            </select>
                            @error('primary_department_id') <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="{{ $label }}">Additional Departments (optional)</label>
                            <div class="space-y-2 max-h-56 overflow-y-auto border rounded-lg p-2">
                                @forelse($departments as $d)
                                @continue($primary_department_id == $d['department_id'])
                                <label class="flex items-center gap-2">
                                    <input type="checkbox"
                                        value="{{ $d['department_id'] }}"
                                        wire:model.live="additional_departments" />
                                    <span>{{ $d['department_name'] }}</span>
                                </label>
                                @empty
                                <p class="text-sm text-gray-500">No departments in your company.</p>
                                @endforelse
                            </div>
                            @error('additional_departments') <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="{{ $label }}">Full Name</label>
                            <input type="text" class="{{ $input }}" wire:model.defer="full_name" placeholder="e.g. John Doe">
                            @error('full_name') <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="{{ $label }}">Email Address</label>
                            <input type="email" class="{{ $input }}" wire:model.defer="email" placeholder="e.g. john@company.com">
                            @error('email') <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="{{ $label }}">Phone Number</label>
                            <input type="text" class="{{ $input }}" wire:model.defer="phone_number" placeholder="08123456789">
                            @error('phone_number') <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="{{ $label }}">Password</label>
                            <input type="password" class="{{ $input }}" wire:model.defer="password" autocomplete="new-password">
                            @error('password') <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="{{ $label }}">Confirm Password</label>
                            <input type="password" class="{{ $input }}" wire:model.defer="password_confirmation">
                        </div>
                    </div>

                    <div class="pt-5">
                        <button type="submit" class="inline-flex items-center gap-2 px-5 h-10 rounded-xl bg-gray-900 text-white text-sm font-medium hover:bg-black">
                            Simpan Data
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </main>

    {{-- MODAL EDIT --}}
    @if($showEditModal)
    <div class="fixed inset-0 z-[60] flex items-center justify-center" wire:keydown.escape.window="closeEdit">
        <button type="button" class="absolute inset-0 bg-black/50" wire:click="closeEdit"></button>

        <div class="relative w-full max-w-2xl mx-4 {{ $card }}">
            <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-base font-semibold text-gray-900">Edit Admin</h3>
                <button class="text-gray-500 hover:text-gray-700" wire:click="closeEditModal" aria-label="Close">
                    <x-heroicon-o-x-mark class="w-5 h-5" />
                </button>
            </div>

            <form class="p-5" wire:submit.prevent="update">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    {{-- Full Name --}}
                    <div>
                        <label class="{{ $label }}">Full Name</label>
                        <input type="text" class="{{ $input }}" wire:model.defer="full_name">
                        @error('full_name') <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p> @enderror
                    </div>

                    {{-- Email --}}
                    <div>
                        <label class="{{ $label }}">Email Address</label>
                        <input type="email" class="{{ $input }}" wire:model.defer="email">
                        @error('email') <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p> @enderror
                    </div>

                    {{-- Phone --}}
                    <div>
                        <label class="{{ $label }}">Phone Number</label>
                        <input type="text" class="{{ $input }}" wire:model.defer="phone_number">
                        @error('phone_number') <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p> @enderror
                    </div>

                    {{-- New Password (optional) --}}
                    <div>
                        <label class="{{ $label }}">Password (kosongkan jika tidak diubah)</label>
                        <input type="password" class="{{ $input }}" wire:model.defer="password" autocomplete="new-password">
                        @error('password') <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p> @enderror
                    </div>

                    {{-- Primary Department --}}
                    <div>
                        <label class="{{ $label }}">Primary Department</label>
                        <select class="{{ $input }}" wire:model.live="primary_department_id">
                            <option value="">— Pilih primary —</option>
                            @foreach($departments as $d)
                            <option value="{{ $d['department_id'] }}">{{ $d['department_name'] }}</option>
                            @endforeach
                        </select>
                        @error('primary_department_id') <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p> @enderror
                    </div>

                    {{-- Additional Departments (grid 2 kolom, no limit) --}}
                    <div>
                        <label class="{{ $label }}">Additional Departments (optional)</label>
                        <div class="border rounded-lg p-2 max-h-60 overflow-y-auto">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                @forelse($departments as $d)
                                @continue($primary_department_id == $d['department_id'])
                                <label class="flex items-center gap-2">
                                    <input type="checkbox"
                                        value="{{ $d['department_id'] }}"
                                        wire:model.live="additional_departments" />
                                    <span class="truncate">{{ $d['department_name'] }}</span>
                                </label>
                                @empty
                                <p class="text-sm text-gray-500 col-span-2">No departments in your company.</p>
                                @endforelse
                            </div>
                        </div>
                        @error('additional_departments') <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end gap-3 border-t border-gray-200 pt-4">
                    <button type="button"
                        class="px-4 h-10 rounded-xl border border-gray-300 text-gray-700 text-sm font-medium hover:bg-gray-100"
                        wire:click="closeEditModal">
                        Batal
                    </button>
                    <button type="submit" wire:loading.attr="disabled" wire:target="update"
                        class="inline-flex items-center gap-2 px-5 h-10 rounded-xl bg-gray-900 text-white text-sm font-medium hover:bg-black disabled:opacity-60">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
    {{-- /MODAL EDIT --}}

</div>