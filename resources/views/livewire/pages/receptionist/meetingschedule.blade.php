<div class="bg-gray-50 min-h-screen">
    @php
    $card = 'bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden';
    $label = 'block text-sm font-medium text-gray-700 mb-2';
    $input = 'w-full h-10 px-3 rounded-lg border border-gray-300 text-gray-800 placeholder:text-gray-400 focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10 bg-white transition';
    $btnBlk = 'px-5 h-10 rounded-xl bg-gray-900 text-white text-sm font-medium shadow-sm focus:outline-none focus:ring-2 focus:ring-gray-900/20 transition active:scale-95 hover:bg-black disabled:opacity-60';

    $otherId = $otherRequirementId ?? null;
    @endphp

    <main class="px-4 sm:px-6 py-6 space-y-6">
        {{-- Header Card --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-gray-900 to-black text-white shadow-2xl">
            <div class="relative z-10 p-6 sm:p-8">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm border border-white/20">
                        <x-heroicon-o-calendar-days class="w-6 h-6 text-white" />
                    </div>
                    <div>
                        <h2 class="text-lg sm:text-xl font-semibold">Meeting Schedule</h2>
                        <p class="text-sm text-white/80">Form Booking Ruangan & Online Meeting.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- FORM: BOOKING ROOM (OFFLINE) --}}
        <section class="{{ $card }}">
            <div class="px-5 py-4 border-b border-gray-200">
                <h3 class="text-base font-semibold text-gray-900">Tambah Booking Room (Offline)</h3>
                <p class="text-sm text-gray-500">Saat disimpan akan masuk <b>Pending</b> (menunggu approval).</p>
            </div>

            <form class="p-5" wire:submit.prevent="saveOffline">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div class="md:col-span-3">
                        <label class="{{ $label }}">Meeting Title</label>
                        <input type="text" wire:model.defer="form.meeting_title" class="{{ $input }}" placeholder="Contoh: Weekly Sync">
                        @error('form.meeting_title') <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="{{ $label }}">Room</label>
                        <select wire:model.defer="form.room_id" class="{{ $input }}">
                            <option value="" hidden>Pilih room</option>
                            @foreach ($rooms as $r)
                            <option value="{{ $r['id'] }}">{{ $r['name'] }}</option>
                            @endforeach
                        </select>
                        @error('form.room_id') <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p> @enderror
                    </div>

                    {{-- Department with SEARCH filter (OFFLINE) --}}
                    <div>
                        <label class="{{ $label }}">Departemen</label>
                        <input type="text" wire:model.live="deptQueryOffline" class="{{ $input }}" placeholder="Cari departemen…">
                        <select wire:model.live="form.department_id" class="{{ $input }} mt-2">
                            <option value="" hidden>Pilih departemen</option>
                            @forelse ($departmentsOffline as $d)
                            <option value="{{ $d['id'] }}">{{ $d['name'] }}</option>
                            @empty
                            <option value="" disabled>Tidak ada hasil</option>
                            @endforelse
                        </select>
                        @error('form.department_id') <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p> @enderror
                    </div>

                    {{-- User with SEARCH (OFFLINE) --}}
                    <div>
                        <label class="{{ $label }}">User (filtered by department)</label>
                        <input type="text" wire:model.live="userQueryOffline" class="{{ $input }}" placeholder="Cari user…">
                        <select wire:model.live="offline_user_id" class="{{ $input }} mt-2">
                            <option value="">— Select User —</option>
                            @forelse ($usersByDeptOffline as $u)
                            <option wire:key="off-u-{{ $u['id'] }}" value="{{ $u['id'] }}">{{ $u['name'] }}</option>
                            @empty
                            <option value="" disabled>— No users found —</option>
                            @endforelse
                        </select>
                        @error('offline_user_id') <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="{{ $label }}">Tanggal</label>
                        <input type="date" wire:model.defer="form.date" class="{{ $input }}">
                        @error('form.date') <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="{{ $label }}">Peserta</label>
                        <input type="number" min="1" wire:model.defer="form.participant" class="{{ $input }}">
                        @error('form.participant') <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="{{ $label }}">Mulai</label>
                        <input type="time" wire:model.defer="form.time" class="{{ $input }}">
                        @error('form.time') <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="{{ $label }}">Selesai</label>
                        <input type="time" wire:model.defer="form.time_end" class="{{ $input }}">
                        @error('form.time_end') <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-3">
                        <label class="{{ $label }}">Kebutuhan Ruangan</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-8 gap-y-3 text-sm text-gray-700">
                            @foreach ($requirementOptions as $opt)
                            @if ($opt['id'] !== $otherId)
                            <label class="inline-flex items-center gap-2 cursor-pointer" wire:key="req-{{ $opt['id'] }}">
                                <input type="checkbox" value="{{ $opt['id'] }}" wire:model.live="form.requirements" class="rounded border-gray-300 text-gray-900 focus:ring-gray-900">
                                <span>{{ $opt['name'] }}</span>
                            </label>
                            @endif
                            @endforeach
                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" wire:model.live="form.requirements" value="Other"
                                    class="rounded border-gray-300 text-gray-900 focus:ring-gray-900">
                                <span class="text-sm text-gray-700">Other</span>
                            </label>
                        </div>
                        @error('form.requirements.*') <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Conditional display: Show notes if the string 'Other' is in the requirements array --}}
                @if (in_array('Other', $form['requirements'] ?? [], true))
                    <div class="mt-4">
                        <label class="block text-xs font-medium text-gray-900 mb-1.5">Special Notes</label>
                        <textarea wire:model.defer="form.notes" rows="3" placeholder="Please specify your other requirement…"
                            class="w-full px-3 py-2 text-sm text-gray-900 placeholder:text-gray-400 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent resize-none"></textarea>
                        @error('form.notes') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                @endif
                
                {{-- Inform Information Dept Checkbox for OFFLINE --}}
                <div class="pt-4">
                    <label class="inline-flex items-start gap-3">
                        <input type="checkbox" wire:model.defer="informInfoOffline"
                            class="mt-0.5 rounded border-gray-300 text-gray-900 focus:ring-gray-900">
                        <span class="text-sm text-gray-700">
                            Minta Information Dept menginformasikan meeting ini (<span class="font-semibold text-gray-900">request</span>)
                        </span>
                    </label>
                    @error('informInfoOffline') <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p> @enderror
                </div>

                <div class="pt-5">
                    <button type="submit" class="inline-flex items-center gap-2 {{ $btnBlk }}" wire:loading.attr="disabled">
                        <x-heroicon-o-check class="w-4 h-4" />
                        Simpan Data Booking Room
                    </button>
                </div>
            </form>
        </section>

        {{-- FORM: ONLINE MEETING --}}
        <section class="{{ $card }}">
            <div class="px-5 py-4 border-b border-gray-200">
                <div class="flex items-center gap-3">
                    <div class="w-2 h-2 bg-blue-600 rounded-full"></div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">Create Online Meeting</h3>
                        <p class="text-sm text-gray-500">Form terpisah untuk meeting online. Status approval di halaman lain.</p>
                    </div>
                </div>
            </div>

            <form class="p-5 grid grid-cols-1 lg:grid-cols-2 gap-6" wire:submit.prevent="saveOnline">
                <div class="space-y-4">
                    <div>
                        <label class="{{ $label }}">Meeting Title</label>
                        <input type="text" wire:model.defer="online_meeting_title" class="{{ $input }}" placeholder="Contoh: Standup harian">
                        @error('online_meeting_title') <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="{{ $label }}">Platform</label>
                            <select wire:model.live="online_platform" class="{{ $input }}">
                                <option value="google_meet">Google Meet</option>
                                <option value="zoom">Zoom</option>
                            </select>
                            @error('online_platform') <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p> @enderror
                        </div>
                        <div class="flex items-end">
                            @if($online_platform === 'google_meet')
                            <span class="text-[11px] px-2 py-1 rounded {{ $googleConnected ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                {{ $googleConnected ? 'Google connected' : 'Google NOT connected' }}
                            </span>
                            @endif
                        </div>
                    </div>

                    {{-- Department with SEARCH (ONLINE) --}}
                    <div>
                        <label class="{{ $label }}">Department</label>
                        <input type="text" wire:model.live="deptQueryOnline" class="{{ $input }}" placeholder="Search department…">
                        <select wire:model.live="online_department_id" class="{{ $input }} mt-2">
                            <option value="">— Select Department (Optional) —</option>
                            @forelse($departmentsOnline as $d)
                            <option value="{{ $d['id'] }}">{{ $d['name'] }}</option>
                            @empty
                            <option value="" disabled>No results</option>
                            @endforelse
                        </select>
                        @error('online_department_id') <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p> @enderror
                    </div>

                    {{-- User with SEARCH (ONLINE) --}}
                    <div>
                        <label class="{{ $label }}">User (filtered by department, Optional)</label>
                        <input type="text" wire:model.live="userQueryOnline" class="{{ $input }}" placeholder="Cari user…">
                        <select wire:model.live="online_user_id" class="{{ $input }} mt-2">
                            <option value="">— Select User —</option>
                            @forelse($usersByDept as $u)
                            <option wire:key="on-u-{{ $u['id'] }}" value="{{ $u['id'] }}">{{ $u['name'] }}</option>
                            @empty
                            <option value="" disabled>— No users found —</option>
                            @endforelse
                        </select>
                        @error('online_user_id') <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="{{ $label }}">Date</label>
                            <input type="date" wire:model.defer="online_date" class="{{ $input }}">
                            @error('online_date') <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="{{ $label }}">Start</label>
                            <input type="time" wire:model.defer="online_start_time" class="{{ $input }}">
                            @error('online_start_time') <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="{{ $label }}">End</label>
                            <input type="time" wire:model.defer="online_end_time" class="{{ $input }}">
                            @error('online_end_time') <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Inform Information Dept Checkbox for ONLINE --}}
                    <div class="pt-2">
                        <label class="inline-flex items-start gap-3">
                            <input type="checkbox" wire:model.defer="informInfoOnline"
                                class="mt-0.5 rounded border-gray-300 text-gray-900 focus:ring-gray-900">
                            <span class="text-sm text-gray-700">
                                Minta Information Dept menginformasikan meeting ini (<span class="font-semibold text-gray-900">request</span>)
                            </span>
                        </label>
                        @error('informInfoOnline') <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p> @enderror
                    </div>

                    <div class="pt-5 lg:pt-1">
                        <button type="submit" class="inline-flex items-center gap-2 {{ $btnBlk }}" wire:loading.attr="disabled">
                            <x-heroicon-o-link class="w-4 h-4" />
                            Submit Online Meeting
                        </button>
                    </div>
                </div>
            </form>
        </section>
    </main>
</div>