<div class="bg-gray-50">
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
                    <div
                        class="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm border border-white/20">
                        <x-heroicon-o-user-group class="w-6 h-6 text-white" />
                    </div>
                    <div>
                        <h2 class="text-lg sm:text-xl font-semibold">User Management</h2>
                        <p class="text-sm text-white/80">
                            Cabang: <span class="font-semibold">{{ $company_name }}</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- FORM CREATE --}}
        <section class="{{ $card }}">
            <div class="px-5 py-4 border-b border-gray-200">
                <h3 class="text-base font-semibold text-gray-900">Tambah User</h3>
                <p class="text-sm text-gray-500">Isi form untuk menambahkan user baru.</p>
            </div>

            <form class="p-5" wire:submit.prevent="store">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
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
                        <label class="{{ $label }}">Company</label>
                        <input type="text" class="{{ $input }}" value="{{ $company_name }}" readonly>
                    </div>

                    {{-- ROLE --}}
                    <div>
                        <label class="{{ $label }}">Role</label>
                        <select class="{{ $input }}" wire:model.live="role_key">
                            <option value="">Pilih role</option>
                            @foreach ($roleOptions as $r)
                                <option value="{{ $r['key'] }}">{{ $r['name'] }}</option>
                            @endforeach
                        </select>
                        @php
                            $roleId = null;
                            if ($role_key) {
                                [$roleId] = explode('_', $role_key);
                            }
                        @endphp
                        @if($roleId == $roleReceptionistId || $roleId == $roleSuperadminId)
                            <p class="mt-1 text-xs text-gray-500">Department akan diisi otomatis.</p>
                        @endif
                        @error('role_key') <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p> @enderror
                    </div>

                    {{-- DEPARTMENT --}}
                    <div class="md:col-span-2">
                        <label class="{{ $label }}">Department</label>
                        @php
                            $roleId = null;
                            if ($role_key) {
                                [$roleId] = explode('_', $role_key);
                            }
                        @endphp
                        @if($roleId == $roleReceptionistId || $roleId == $roleSuperadminId)
                            <input type="text" value="Tidak diperlukan untuk peran ini" disabled
                                class="{{ $input }} bg-gray-100 text-gray-500 border-gray-200 cursor-not-allowed">
                            <input type="hidden" wire:model="department_id">
                            <p class="mt-1 text-xs text-gray-500">Department tidak diperlukan untuk role ini.</p>
                        @else
                            <select class="{{ $input }}" wire:model.defer="department_id">
                                <option value="">Pilih department</option>
                                @foreach ($departments as $d)
                                    <option value="{{ $d->department_id }}">{{ $d->department_name }}</option>
                                @endforeach
                            </select>
                            @error('department_id') <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p> @enderror
                        @endif
                    </div>
                </div>

                <div class="pt-5">
                    <button type="submit" wire:loading.attr="disabled" wire:target="store"
                        class="inline-flex items-center gap-2 px-5 h-10 rounded-xl bg-gray-900 text-white text-sm font-medium shadow-sm focus:outline-none focus:ring-2 focus:ring-gray-900/20 transition active:scale-95 hover:bg-black disabled:opacity-60 relative overflow-hidden"
                        wire:loading.class="opacity-80 cursor-wait">
                        <span class="flex items-center gap-2" wire:loading.remove wire:target="store">
                            <x-heroicon-o-check class="w-4 h-4" />
                            Simpan Data
                        </span>
                        <span class="flex items-center gap-2" wire:loading wire:target="store">
                            <x-heroicon-o-arrow-path class="h-4 w-4 animate-spin" />
                            Menyimpan…
                        </span>
                    </button>
                </div>
            </form>
        </section>

        {{-- LIST USERS --}}
        <div class="{{ $card }}">
            <div class="px-5 py-4 border-b border-gray-200">
                <div class="flex flex-col lg:flex-row lg:items-center gap-4">
                    <div class="relative flex-1">
                        <input type="text" wire:model.live="search" placeholder="Cari nama atau email..."
                            class="{{ $input }} pl-10 w-full placeholder:text-gray-400">
                        <x-heroicon-o-magnifying-glass class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                    </div>

                    <div class="relative">
                        <select wire:model.live="roleFilter" class="{{ $input }} pl-10 w-full lg:w-60">
                            <option value="">Semua Role</option>
                            @foreach ($roles as $r)
                                <option value="{{ $r['id'] }}">{{ $r['name'] }}</option>
                            @endforeach
                        </select>
                        <x-heroicon-o-users class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                    </div>

                    {{-- DEPARTMENT FILTER (NEWLY ADDED) --}}
                    <div class="relative">
                        <select wire:model.live="departmentFilter" class="{{ $input }} pl-10 w-full lg:w-60">
                            <option value="">Semua Department</option>
                            @foreach ($departments as $d)
                                <option value="{{ $d->department_id }}">{{ $d->department_name }}</option>
                            @endforeach
                        </select>
                        <x-heroicon-o-bars-4 class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                    </div>

                </div>
            </div>

            <div class="divide-y divide-gray-200">
                @forelse ($users as $u)
                    @php $rowNo = (($users->firstItem() ?? 1) - 0) + $loop->index; @endphp
                    <div class="px-5 py-5 hover:bg-gray-50 transition-colors" wire:key="user-{{ $u->user_id }}">
                        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                            <div class="flex items-start gap-3 flex-1">
                                <div class="{{ $ico }}">{{ strtoupper(substr($u->full_name, 0, 1)) }}</div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2">
                                        <h4 class="font-semibold text-gray-900 text-sm sm:text-base truncate">
                                            {{ $u->full_name }}
                                        </h4>
                                        <span class="{{ $chip }}">
                                            <span class="font-medium text-gray-700">{{ $u->role->name ?? 'No Role' }}</span>
                                        </span>
                                        <span class="{{ $chip }}">
                                            <span class="text-gray-500">Dept:</span>
                                            <span class="font-medium text-gray-700">{{ $u->department->department_name ?? 'N/A' }}</span>
                                        </span>
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
                                    <button class="{{ $btnBlk }}" wire:click="openEdit({{ $u->user_id }})">
                                        Edit
                                    </button>
                                    <button class="{{ $btnRed }}" wire:click="delete({{ $u->user_id }})"
                                        onclick="return confirm('Hapus user ini?')">
                                        Hapus
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-14 text-center text-gray-500 text-sm">Tidak ada user yang cocok.</div>
                @endforelse
            </div>

            @if($users->hasPages())
                <div class="px-5 py-4 bg-gray-50 border-t border-gray-200">
                    <div class="flex justify-center">
                        {{ $users->links() }}
                    </div>
                </div>
            @endif
        </div>

        {{-- MODAL EDIT --}}
        @if($modalEdit)
            <div class="fixed inset-0 z-[60] flex items-center justify-center" wire:keydown.escape.window="closeEdit">
                <button type="button" class="absolute inset-0 bg-black/50" wire:click="closeEdit"></button>
                <div class="relative w-full max-w-2xl mx-4 {{ $card }}">
                    <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                        <h3 class="text-base font-semibold text-gray-900">Edit User</h3>
                        <button class="text-gray-500 hover:text-gray-700" wire:click="closeEdit">
                            <x-heroicon-o-x-mark class="w-5 h-5" />
                        </button>
                    </div>

                    <form class="p-5" wire:submit.prevent="update">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="{{ $label }}">Full Name</label>
                                <input type="text" class="{{ $input }}" wire:model.defer="edit_full_name">
                                @error('edit_full_name') <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="{{ $label }}">Email Address</label>
                                <input type="email" class="{{ $input }}" wire:model.defer="edit_email">
                                @error('edit_email') <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="{{ $label }}">Phone Number</label>
                                <input type="text" class="{{ $input }}" wire:model.defer="edit_phone_number">
                                @error('edit_phone_number') <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="{{ $label }}">Password (kosongkan jika tidak diubah)</label>
                                <input type="password" class="{{ $input }}" wire:model.defer="edit_password" autocomplete="new-password">
                            </div>

                            {{-- ROLE --}}
                            <div>
                                <label class="{{ $label }}">Role</label>
                                <select class="{{ $input }}" wire:model.live="edit_role_key">
                                    <option value="">Pilih role</option>
                                    @foreach ($roleOptions as $r)
                                        <option value="{{ $r['key'] }}">{{ $r['name'] }}</option>
                                    @endforeach
                                </select>
                                @php
                                    $editRoleId = null;
                                    if ($edit_role_key) {
                                        [$editRoleId] = explode('_', $edit_role_key);
                                    }
                                @endphp
                                @if($editRoleId == $roleReceptionistId || $editRoleId == $roleSuperadminId)
                                    <p class="mt-1 text-xs text-gray-500">Department akan diisi otomatis.</p>
                                @endif
                                @error('edit_role_key') <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p> @enderror
                            </div>

                            {{-- DEPARTMENT --}}
                            <div>
                                <label class="{{ $label }}">Department</label>
                                @php
                                    $editRoleId = null;
                                    if ($edit_role_key) {
                                        [$editRoleId] = explode('_', $edit_role_key);
                                    }
                                @endphp
                                @if($editRoleId == $roleReceptionistId || $editRoleId == $roleSuperadminId)
                                    <input type="text" value="Tidak diperlukan untuk peran ini" disabled
                                        class="{{ $input }} bg-gray-100 text-gray-500 border-gray-200 cursor-not-allowed">
                                    <input type="hidden" wire:model="edit_department_id">
                                    <p class="mt-1 text-xs text-gray-500">Department tidak diperlukan untuk role ini.</p>
                                @else
                                    <select class="{{ $input }}" wire:model.defer="edit_department_id">
                                        <option value="">Pilih department</option>
                                        @foreach ($departments as $d)
                                            <option value="{{ $d->department_id }}">{{ $d->department_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('edit_department_id') <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p> @enderror
                                @endif
                            </div>
                        </div>

                        <div class="mt-6 flex items-center justify-end gap-3 border-t border-gray-200 pt-4">
                            <button type="button" class="px-4 h-10 rounded-xl border border-gray-300 text-gray-700 text-sm font-medium hover:bg-gray-100" wire:click="closeEdit">
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
    </main>
</div>