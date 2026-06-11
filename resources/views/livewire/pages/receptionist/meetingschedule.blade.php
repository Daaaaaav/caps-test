<div class="min-h-screen bg-background">
    @php
        $card   = 'bg-card border border-border rounded-2xl shadow-xl overflow-hidden';
        $label  = 'block text-xs font-semibold uppercase tracking-wider text-muted-foreground mb-1.5';
        $input  = 'w-full h-10 px-3.5 rounded-lg border border-input bg-background text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all';
        $btnBlk = 'inline-flex items-center justify-center gap-2 px-5 h-10 text-xs font-semibold rounded-lg bg-primary text-primary-foreground hover:bg-primary/95 transition shadow-sm focus:outline-none focus:ring-2 focus:ring-primary/20 disabled:opacity-60';

        $otherId = $otherRequirementId ?? null;
    @endphp

    <style>
      :root { color-scheme: light; }
      select, option {
        color: var(--foreground) !important;
        background: var(--background) !important;
      }
      option:checked { background: var(--muted) !important; color: var(--foreground) !important; }
    </style>

    <main class="px-4 sm:px-6 py-6 space-y-6">
        {{-- Header Card --}}
        <div class="relative overflow-hidden rounded-2xl bg-[#4A2F24] text-[#CDDEA7] shadow-2xl">
            <div class="pointer-events-none absolute inset-0 opacity-10">
                <div class="absolute top-0 -right-4 w-24 h-24 bg-[#CDDEA7] rounded-full blur-xl"></div>
                <div class="absolute bottom-0 -left-4 w-16 h-16 bg-[#CDDEA7] rounded-full blur-lg"></div>
            </div>
            <div class="relative z-10 p-6 sm:p-8">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-[#CDDEA7]/10 rounded-xl flex items-center justify-center backdrop-blur-sm border border-[#CDDEA7]/20">
                        <x-heroicon-o-calendar-days class="w-6 h-6 text-[#CDDEA7]" />
                    </div>
                    <div>
                        <h2 class="text-lg sm:text-xl font-semibold">{{ __('app.meeting_schedule_title') }}</h2>
                        <p class="text-xs text-[#CDDEA7]/80">{{ __('app.meeting_schedule_sub') }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- FORM: BOOKING ROOM (OFFLINE) --}}
        <section class="{{ $card }}">
            <button type="button" wire:click="$toggle('showOfflineForm')"
                class="w-full flex items-center justify-between px-6 py-5 border-b border-border bg-muted/10 hover:bg-muted/20 transition text-left focus:outline-none">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-primary/10 flex items-center justify-center">
                        <x-heroicon-o-calendar class="w-4.5 h-4.5 text-primary" />
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-foreground">{{ __('app.add_booking_offline') }}</h3>
                        <p class="text-xs text-muted-foreground mt-0.5">{{ __('app.add_booking_offline_sub') }}</p>
                    </div>
                </div>
                <svg class="w-5 h-5 text-gray-400 transition-transform duration-300 {{ $showOfflineForm ? 'rotate-180' : '' }}"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            @if($showOfflineForm)
                <form class="p-6 space-y-6" wire:submit.prevent="saveOffline">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div class="md:col-span-3">
                        <label class="{{ $label }}">{{ __('app.title_col') }}</label>
                        <input type="text" wire:model.defer="form.meeting_title" class="{{ $input }}" placeholder="{{ __('app.title_col') }}">
                        @error('form.meeting_title') <p class="mt-1.5 text-xs text-destructive font-medium">{{ $message }}</p> @enderror
                    </div>

                    {{-- Room Combobox --}}
                    <div>
                        <label class="{{ $label }}">{{ __('app.room') }}</label>
                        <div
                            x-data="{
                                open: false,
                                search: '',
                                get items() {
                                    const q = this.search.toLowerCase().trim();
                                    return @js(collect($rooms)->map(fn($r) => ['id' => $r['id'], 'label' => $r['name']])->values()->toArray()).filter(i =>
                                        !q || i.label.toLowerCase().includes(q)
                                    );
                                },
                                select(id, label) {
                                    this.search = label;
                                    $wire.set('form.room_id', id);
                                    this.open = false;
                                },
                                clear() {
                                    this.search = '';
                                    $wire.set('form.room_id', null);
                                }
                            }"
                            x-init="
                                $watch('$wire.form.room_id', val => {
                                    if (!val) { search = ''; }
                                    else {
                                        const found = @js(collect($rooms)->map(fn($r) => ['id' => $r['id'], 'label' => $r['name']])->values()->toArray()).find(i => i.id == val);
                                        if (found) search = found.label;
                                    }
                                });
                            "
                            class="relative"
                            @click.outside="open = false"
                        >
                            <div class="relative">
                                <input
                                    type="text"
                                    x-model="search"
                                    @focus="open = true"
                                    @input="open = true"
                                    @keydown.escape="open = false"
                                    @keydown.enter.prevent="items.length === 1 && select(items[0].id, items[0].label)"
                                    autocomplete="off"
                                    placeholder="{{ __('app.select_room') }}"
                                    class="{{ $input }} pr-8"
                                >
                                <div class="absolute inset-y-0 right-0 flex items-center gap-1 pr-2.5">
                                    <button
                                        x-show="search"
                                        type="button"
                                        @click.stop="clear()"
                                        class="text-muted-foreground hover:text-foreground"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                    <svg class="fill-current h-4 w-4 text-muted-foreground/60 pointer-events-none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                                </div>
                            </div>
                            <ul
                                x-show="open && items.length > 0"
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="opacity-0 -translate-y-1"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                class="absolute z-30 mt-1 w-full max-h-52 overflow-y-auto rounded-lg border border-border bg-card shadow-lg text-sm"
                                style="display:none"
                            >
                                <template x-for="item in items" :key="item.id">
                                    <li
                                        @click="select(item.id, item.label)"
                                        :class="$wire.form.room_id == item.id
                                            ? 'bg-primary text-primary-foreground'
                                            : 'text-foreground hover:bg-muted cursor-pointer'"
                                        class="px-3.5 py-2.5 cursor-pointer transition-colors"
                                        x-text="item.label"
                                    ></li>
                                </template>
                            </ul>
                            <p x-show="open && items.length === 0 && search" class="absolute z-30 mt-1 w-full rounded-lg border border-border bg-card shadow-lg text-sm px-3.5 py-2.5 text-muted-foreground" style="display:none">
                                {{ __('app.no_data') }}
                            </p>
                            <input type="hidden" wire:model="form.room_id">
                        </div>
                        @error('form.room_id') <p class="mt-1.5 text-xs text-destructive font-medium">{{ $message }}</p> @enderror
                    </div>

                    {{-- Department Combobox (OFFLINE) --}}
                    <div>
                        <label class="{{ $label }}">{{ __('app.dept_label') }}</label>
                        <div
                            x-data="{
                                open: false,
                                search: '',
                                get items() {
                                    const q = this.search.toLowerCase().trim();
                                    return @js(collect($departments)->map(fn($d) => ['id' => $d['id'], 'label' => $d['name']])->values()->toArray()).filter(i =>
                                        !q || i.label.toLowerCase().includes(q)
                                    );
                                },
                                select(id, label) {
                                    this.search = label;
                                    $wire.set('form.department_id', id);
                                    this.open = false;
                                },
                                clear() {
                                    this.search = '';
                                    $wire.set('form.department_id', null);
                                }
                            }"
                            x-init="
                                $watch('$wire.form.department_id', val => {
                                    if (!val) { search = ''; }
                                    else {
                                        const found = @js(collect($departments)->map(fn($d) => ['id' => $d['id'], 'label' => $d['name']])->values()->toArray()).find(i => i.id == val);
                                        if (found) search = found.label;
                                    }
                                });
                            "
                            class="relative"
                            @click.outside="open = false"
                        >
                            <div class="relative">
                                <input
                                    type="text"
                                    x-model="search"
                                    @focus="open = true"
                                    @input="open = true"
                                    @keydown.escape="open = false"
                                    @keydown.enter.prevent="items.length === 1 && select(items[0].id, items[0].label)"
                                    autocomplete="off"
                                    placeholder="{{ __('app.search_dept_offline_ph') }}"
                                    class="{{ $input }} pr-8"
                                >
                                <div class="absolute inset-y-0 right-0 flex items-center gap-1 pr-2.5">
                                    <button
                                        x-show="search"
                                        type="button"
                                        @click.stop="clear()"
                                        class="text-muted-foreground hover:text-foreground"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                    <svg class="fill-current h-4 w-4 text-muted-foreground/60 pointer-events-none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                                </div>
                            </div>
                            <ul
                                x-show="open && items.length > 0"
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="opacity-0 -translate-y-1"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                class="absolute z-30 mt-1 w-full max-h-52 overflow-y-auto rounded-lg border border-border bg-card shadow-lg text-sm"
                                style="display:none"
                            >
                                <template x-for="item in items" :key="item.id">
                                    <li
                                        @click="select(item.id, item.label)"
                                        :class="$wire.form.department_id == item.id
                                            ? 'bg-primary text-primary-foreground'
                                            : 'text-foreground hover:bg-muted cursor-pointer'"
                                        class="px-3.5 py-2.5 cursor-pointer transition-colors"
                                        x-text="item.label"
                                    ></li>
                                </template>
                            </ul>
                            <p x-show="open && items.length === 0 && search" class="absolute z-30 mt-1 w-full rounded-lg border border-border bg-card shadow-lg text-sm px-3.5 py-2.5 text-muted-foreground" style="display:none">
                                {{ __('app.no_data') }}
                            </p>
                            <input type="hidden" wire:model="form.department_id">
                        </div>
                        @error('form.department_id') <p class="mt-1.5 text-xs text-destructive font-medium">{{ $message }}</p> @enderror
                    </div>

                    {{-- User Combobox (OFFLINE, filtered by department) --}}
                    <div>
                        <label class="{{ $label }}">{{ __('app.user_filtered_dept') }}</label>
                        <div
                            x-data="{
                                open: false,
                                search: '',
                                get items() {
                                    const q = this.search.toLowerCase().trim();
                                    const list = ($wire.usersByDeptOffline || []).map(u => ({ id: u.id, label: u.name }));
                                    return q ? list.filter(i => i.label.toLowerCase().includes(q)) : list;
                                },
                                select(id, label) {
                                    this.search = label;
                                    $wire.set('offline_user_id', id);
                                    this.open = false;
                                },
                                clear() {
                                    this.search = '';
                                    $wire.set('offline_user_id', null);
                                }
                            }"
                            x-init="
                                $watch('$wire.form.department_id', () => { search = ''; $wire.set('offline_user_id', null); });
                            "
                            class="relative"
                            @click.outside="open = false"
                        >
                            <div class="relative">
                                <input
                                    type="text"
                                    x-model="search"
                                    @focus="if ($wire.form.department_id) open = true"
                                    @input="if ($wire.form.department_id) open = true"
                                    @keydown.escape="open = false"
                                    @keydown.enter.prevent="items.length === 1 && select(items[0].id, items[0].label)"
                                    autocomplete="off"
                                    placeholder="{{ __('app.search_user_offline_ph') }}"
                                    :disabled="!$wire.form.department_id"
                                    class="{{ $input }} pr-8 disabled:bg-muted disabled:text-muted-foreground disabled:cursor-not-allowed"
                                >
                                <div class="absolute inset-y-0 right-0 flex items-center gap-1 pr-2.5">
                                    <button
                                        x-show="search"
                                        type="button"
                                        @click.stop="clear()"
                                        class="text-muted-foreground hover:text-foreground"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                    <svg class="fill-current h-4 w-4 text-muted-foreground/60 pointer-events-none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                                </div>
                            </div>
                            <ul
                                x-show="open && items.length > 0"
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="opacity-0 -translate-y-1"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                class="absolute z-30 mt-1 w-full max-h-52 overflow-y-auto rounded-lg border border-border bg-card shadow-lg text-sm"
                                style="display:none"
                            >
                                <template x-for="item in items" :key="item.id">
                                    <li
                                        @click="select(item.id, item.label)"
                                        :class="$wire.offline_user_id == item.id
                                            ? 'bg-primary text-primary-foreground'
                                            : 'text-foreground hover:bg-muted cursor-pointer'"
                                        class="px-3.5 py-2.5 cursor-pointer transition-colors"
                                        x-text="item.label"
                                    ></li>
                                </template>
                            </ul>
                            <p x-show="open && items.length === 0 && search" class="absolute z-30 mt-1 w-full rounded-lg border border-border bg-card shadow-lg text-sm px-3.5 py-2.5 text-muted-foreground" style="display:none">
                                {{ __('app.no_data') }}
                            </p>
                            <input type="hidden" wire:model="offline_user_id">
                        </div>
                        @error('offline_user_id') <p class="mt-1.5 text-xs text-destructive font-medium">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="{{ $label }}">{{ __('app.date_field') }}</label>
                        <input type="date" wire:model.defer="form.date" class="{{ $input }}">
                        @error('form.date') <p class="mt-1.5 text-xs text-destructive font-medium">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="{{ $label }}">{{ __('app.participants_label') }}</label>
                        <input type="number" min="1" wire:model.defer="form.participant" class="{{ $input }}">
                        @error('form.participant') <p class="mt-1.5 text-xs text-destructive font-medium">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="{{ $label }}">{{ __('app.start_label') }}</label>
                        <input type="time" wire:model.defer="form.time" class="{{ $input }}">
                        @error('form.time') <p class="mt-1.5 text-xs text-destructive font-medium">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="{{ $label }}">{{ __('app.end_label') }}</label>
                        <input type="time" wire:model.defer="form.time_end" class="{{ $input }}">
                        @error('form.time_end') <p class="mt-1.5 text-xs text-destructive font-medium">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-3">
                        <label class="{{ $label }}">{{ __('app.room_requirements_label') }}</label>
                        @php
                            $reqNameMap = [
                                'projector & screen' => __('app.req_projector_screen'),
                                'whiteboard'         => __('app.req_whiteboard'),
                                'coffee break'       => __('app.req_coffee_break'),
                                'lunch set'          => __('app.req_lunch_set'),
                                'sound system'       => __('app.req_sound_system'),
                                'other'              => __('app.req_other'),
                            ];
                        @endphp
                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 bg-muted/20 border border-border rounded-2xl p-4">
                            @foreach ($requirementOptions as $opt)
                                @if ($opt['id'] !== $otherId)
                                    @php $reqLabel = $reqNameMap[strtolower($opt['name'])] ?? $opt['name']; @endphp
                                    <label class="flex items-center space-x-2.5 cursor-pointer group" wire:key="req-{{ $opt['id'] }}">
                                        <input type="checkbox" value="{{ $opt['id'] }}" wire:model.live="form.requirements" class="w-4 h-4 rounded border-input text-primary focus:ring-primary/20 bg-background transition-all">
                                        <span class="text-xs text-foreground group-hover:text-primary transition-colors">{{ $reqLabel }}</span>
                                    </label>
                                @endif
                            @endforeach
                            <label class="flex items-center space-x-2.5 cursor-pointer group">
                                <input type="checkbox" wire:model.live="form.requirements" value="Other"
                                    class="w-4 h-4 rounded border-input text-primary focus:ring-primary/20 bg-background transition-all">
                                <span class="text-xs text-foreground group-hover:text-primary transition-colors">{{ __('app.req_other') }}</span>
                            </label>
                        </div>
                        @error('form.requirements.*') <p class="mt-1.5 text-xs text-destructive font-medium">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Conditional display: Show notes if the string 'Other' is in the requirements array --}}
                @if (in_array('Other', $form['requirements'] ?? [], true))
                    <div class="mt-4 bg-primary/5 border border-primary/20 rounded-2xl p-5">
                        <label class="{{ $label }}">{{ __('app.special_notes') }}</label>
                        <textarea wire:model.defer="form.notes" rows="3" placeholder="{{ __('app.special_notes') }}..."
                            class="w-full px-3.5 py-2.5 border border-input rounded-lg bg-background text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all resize-none"></textarea>
                        @error('form.notes') <p class="mt-1.5 text-xs text-destructive font-medium">{{ $message }}</p> @enderror
                    </div>
                @endif
                
                {{-- Inform Information Dept Checkbox for OFFLINE --}}
                <div class="pt-4">
                    <label class="inline-flex items-center gap-2.5 cursor-pointer group">
                        <input type="checkbox" wire:model.defer="informInfoOffline"
                            class="w-4 h-4 rounded border-input text-primary focus:ring-primary/20 bg-background transition-all">
                        <span class="text-xs text-muted-foreground font-semibold group-hover:text-primary transition-colors">
                            {{ __('app.inform_info_dept') }}
                        </span>
                    </label>
                    @error('informInfoOffline') <p class="mt-1.5 text-xs text-destructive font-medium">{{ $message }}</p> @enderror
                </div>

                <div class="pt-5 border-t border-border bg-muted/5 -mx-6 -mb-6 p-6 flex items-center justify-end">
                    <button type="submit" class="inline-flex items-center gap-2 {{ $btnBlk }}" wire:loading.attr="disabled">
                        <x-heroicon-o-check class="w-4 h-4" />
                        <span>{{ __('app.save_booking') }}</span>
                    </button>
                </div>
            </form>
            @endif
        </section>

        {{-- FORM: ONLINE MEETING --}}
        <section class="{{ $card }}">
            <button type="button" wire:click="$toggle('showOnlineForm')"
                class="w-full flex items-center justify-between px-6 py-5 border-b border-border bg-muted/10 hover:bg-muted/20 transition text-left focus:outline-none">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-primary/10 flex items-center justify-center">
                        <x-heroicon-o-video-camera class="w-4.5 h-4.5 text-primary" />
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-foreground">{{ __('app.create_online_meeting') }}</h3>
                        <p class="text-xs text-muted-foreground mt-0.5">{{ __('app.create_online_sub') }}</p>
                    </div>
                </div>
                <svg class="w-5 h-5 text-gray-400 transition-transform duration-300 {{ $showOnlineForm ? 'rotate-180' : '' }}"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            @if($showOnlineForm)
                <form class="p-6 grid grid-cols-1 lg:grid-cols-2 gap-6" wire:submit.prevent="saveOnline">
                <div class="space-y-4">
                    <div>
                        <label class="{{ $label }}">{{ __('app.title_col') }}</label>
                        <input type="text" wire:model.defer="online_meeting_title" class="{{ $input }}" placeholder="{{ __('app.title_col') }}">
                        @error('online_meeting_title') <p class="mt-1.5 text-xs text-destructive font-medium">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="{{ $label }}">Platform</label>
                            <div class="relative">
                                <select wire:model.live="online_platform" class="{{ $input }} appearance-none pr-8">
                                    <option value="google_meet">Google Meet</option>
                                    <option value="zoom">Zoom</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-muted-foreground/60">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                                </div>
                            </div>
                            @error('online_platform') <p class="mt-1.5 text-xs text-destructive font-medium">{{ $message }}</p> @enderror
                        </div>
                        <div class="flex items-end">
                            @if($online_platform === 'google_meet')
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold {{ $googleConnected ? 'bg-emerald-500/10 border border-emerald-500/20 text-emerald-500' : 'bg-yellow-500/10 border border-yellow-500/20 text-yellow-500' }}">
                                    {{ $googleConnected ? __('app.google_connected') : __('app.google_not_connected') }}
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Department Combobox (ONLINE) --}}
                    <div>
                        <label class="{{ $label }}">{{ __('app.dept_label') }}</label>
                        <div
                            x-data="{
                                open: false,
                                search: '',
                                get items() {
                                    const q = this.search.toLowerCase().trim();
                                    return @js(collect($departments)->map(fn($d) => ['id' => $d['id'], 'label' => $d['name']])->values()->toArray()).filter(i =>
                                        !q || i.label.toLowerCase().includes(q)
                                    );
                                },
                                select(id, label) {
                                    this.search = label;
                                    $wire.set('online_department_id', id);
                                    this.open = false;
                                },
                                clear() {
                                    this.search = '';
                                    $wire.set('online_department_id', null);
                                }
                            }"
                            x-init="
                                $watch('$wire.online_department_id', val => {
                                    if (!val) { search = ''; }
                                    else {
                                        const found = @js(collect($departments)->map(fn($d) => ['id' => $d['id'], 'label' => $d['name']])->values()->toArray()).find(i => i.id == val);
                                        if (found) search = found.label;
                                    }
                                });
                            "
                            class="relative"
                            @click.outside="open = false"
                        >
                            <div class="relative">
                                <input
                                    type="text"
                                    x-model="search"
                                    @focus="open = true"
                                    @input="open = true"
                                    @keydown.escape="open = false"
                                    @keydown.enter.prevent="items.length === 1 && select(items[0].id, items[0].label)"
                                    autocomplete="off"
                                    placeholder="{{ __('app.search_department') }}"
                                    class="{{ $input }} pr-8"
                                >
                                <div class="absolute inset-y-0 right-0 flex items-center gap-1 pr-2.5">
                                    <button
                                        x-show="search"
                                        type="button"
                                        @click.stop="clear()"
                                        class="text-muted-foreground hover:text-foreground"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                    <svg class="fill-current h-4 w-4 text-muted-foreground/60 pointer-events-none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                                </div>
                            </div>
                            <ul
                                x-show="open && items.length > 0"
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="opacity-0 -translate-y-1"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                class="absolute z-30 mt-1 w-full max-h-52 overflow-y-auto rounded-lg border border-border bg-card shadow-lg text-sm"
                                style="display:none"
                            >
                                <template x-for="item in items" :key="item.id">
                                    <li
                                        @click="select(item.id, item.label)"
                                        :class="$wire.online_department_id == item.id
                                            ? 'bg-primary text-primary-foreground'
                                            : 'text-foreground hover:bg-muted cursor-pointer'"
                                        class="px-3.5 py-2.5 cursor-pointer transition-colors"
                                        x-text="item.label"
                                    ></li>
                                </template>
                            </ul>
                            <p x-show="open && items.length === 0 && search" class="absolute z-30 mt-1 w-full rounded-lg border border-border bg-card shadow-lg text-sm px-3.5 py-2.5 text-muted-foreground" style="display:none">
                                {{ __('app.no_data') }}
                            </p>
                            <input type="hidden" wire:model="online_department_id">
                        </div>
                        @error('online_department_id') <p class="mt-1.5 text-xs text-destructive font-medium">{{ $message }}</p> @enderror
                    </div>

                    {{-- User Combobox (ONLINE, filtered by department) --}}
                    <div>
                        <label class="{{ $label }}">{{ __('app.user_filtered_optional') }}</label>
                        <div
                            x-data="{
                                open: false,
                                search: '',
                                get items() {
                                    const q = this.search.toLowerCase().trim();
                                    const list = ($wire.usersByDept || []).map(u => ({ id: u.id, label: u.name }));
                                    return q ? list.filter(i => i.label.toLowerCase().includes(q)) : list;
                                },
                                select(id, label) {
                                    this.search = label;
                                    $wire.set('online_user_id', id);
                                    this.open = false;
                                },
                                clear() {
                                    this.search = '';
                                    $wire.set('online_user_id', null);
                                }
                            }"
                            x-init="
                                $watch('$wire.online_department_id', () => { search = ''; $wire.set('online_user_id', null); });
                            "
                            class="relative"
                            @click.outside="open = false"
                        >
                            <div class="relative">
                                <input
                                    type="text"
                                    x-model="search"
                                    @focus="if ($wire.online_department_id) open = true"
                                    @input="if ($wire.online_department_id) open = true"
                                    @keydown.escape="open = false"
                                    @keydown.enter.prevent="items.length === 1 && select(items[0].id, items[0].label)"
                                    autocomplete="off"
                                    placeholder="{{ __('app.search_user_online_ph') }}"
                                    :disabled="!$wire.online_department_id"
                                    class="{{ $input }} pr-8 disabled:bg-muted disabled:text-muted-foreground disabled:cursor-not-allowed"
                                >
                                <div class="absolute inset-y-0 right-0 flex items-center gap-1 pr-2.5">
                                    <button
                                        x-show="search"
                                        type="button"
                                        @click.stop="clear()"
                                        class="text-muted-foreground hover:text-foreground"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                    <svg class="fill-current h-4 w-4 text-muted-foreground/60 pointer-events-none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                                </div>
                            </div>
                            <ul
                                x-show="open && items.length > 0"
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="opacity-0 -translate-y-1"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                class="absolute z-30 mt-1 w-full max-h-52 overflow-y-auto rounded-lg border border-border bg-card shadow-lg text-sm"
                                style="display:none"
                            >
                                <template x-for="item in items" :key="item.id">
                                    <li
                                        @click="select(item.id, item.label)"
                                        :class="$wire.online_user_id == item.id
                                            ? 'bg-primary text-primary-foreground'
                                            : 'text-foreground hover:bg-muted cursor-pointer'"
                                        class="px-3.5 py-2.5 cursor-pointer transition-colors"
                                        x-text="item.label"
                                    ></li>
                                </template>
                            </ul>
                            <p x-show="open && items.length === 0 && search" class="absolute z-30 mt-1 w-full rounded-lg border border-border bg-card shadow-lg text-sm px-3.5 py-2.5 text-muted-foreground" style="display:none">
                                {{ __('app.no_data') }}
                            </p>
                            <input type="hidden" wire:model="online_user_id">
                        </div>
                        @error('online_user_id') <p class="mt-1.5 text-xs text-destructive font-medium">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="space-y-4 flex flex-col justify-between">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="{{ $label }}">{{ __('app.date') }}</label>
                            <input type="date" wire:model.defer="online_date" class="{{ $input }}">
                            @error('online_date') <p class="mt-1.5 text-xs text-destructive font-medium">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="{{ $label }}">{{ __('app.start') }}</label>
                            <input type="time" wire:model.defer="online_start_time" class="{{ $input }}">
                            @error('online_start_time') <p class="mt-1.5 text-xs text-destructive font-medium">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="{{ $label }}">{{ __('app.end') }}</label>
                            <input type="time" wire:model.defer="online_end_time" class="{{ $input }}">
                            @error('online_end_time') <p class="mt-1.5 text-xs text-destructive font-medium">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Inform Information Dept Checkbox for ONLINE --}}
                    <div>
                        <label class="inline-flex items-center gap-2.5 cursor-pointer group">
                            <input type="checkbox" wire:model.defer="informInfoOnline"
                                class="w-4 h-4 rounded border-input text-primary focus:ring-primary/20 bg-background transition-all">
                            <span class="text-xs text-muted-foreground font-semibold group-hover:text-primary transition-colors">
                                {{ __('app.inform_info_dept') }}
                            </span>
                        </label>
                        @error('informInfoOnline') <p class="mt-1.5 text-xs text-destructive font-medium">{{ $message }}</p> @enderror
                    </div>

                    <div class="pt-5 flex items-center justify-end">
                        <button type="submit" class="inline-flex items-center gap-2 {{ $btnBlk }}" wire:loading.attr="disabled">
                            <x-heroicon-o-link class="w-4 h-4" />
                            <span>{{ __('app.submit_online') }}</span>
                        </button>
                    </div>
                </div>
            </form>
            @endif
        </section>
    </main>
</div>