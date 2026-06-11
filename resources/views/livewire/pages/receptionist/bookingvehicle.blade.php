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
                    <h3 class="text-sm font-semibold text-foreground">{{ __('app.booking_form_title') }}</h3>
                    <p class="text-xs text-muted-foreground mt-0.5">{{ __('app.booking_form_subtitle') }}</p>
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
                            <label class="{{ $label }}">{{ __('app.department') }} <span class="text-destructive">*</span></label>

                            {{-- Combobox: search + select in one --}}
                            <div
                                x-data="{
                                    open: false,
                                    search: $wire.departmentSearch,
                                    get items() {
                                        const q = this.search.toLowerCase().trim();
                                        return @js($departments->map(fn($d) => ['id' => $d->department_id, 'label' => $d->department_name])->values()->toArray()).filter(i =>
                                            !q || i.label.toLowerCase().includes(q)
                                        );
                                    },
                                    get selectedLabel() {
                                        const id = $wire.department_id;
                                        const found = @js($departments->map(fn($d) => ['id' => $d->department_id, 'label' => $d->department_name])->values()->toArray()).find(i => i.id == id);
                                        return found ? found.label : '';
                                    },
                                    select(id, label) {
                                        this.search = label;
                                        $wire.set('department_id', id);
                                        this.open = false;
                                    },
                                    clear() {
                                        this.search = '';
                                        $wire.set('department_id', null);
                                        $wire.set('departmentSearch', '');
                                    }
                                }"
                                x-init="
                                    $watch('search', val => $wire.set('departmentSearch', val));
                                    $watch('$wire.department_id', val => {
                                        if (!val) { search = ''; }
                                        else { search = selectedLabel; }
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

                                {{-- Dropdown list --}}
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
                                            :class="$wire.department_id == item.id
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

                                {{-- Hidden real input for Livewire --}}
                                <input type="hidden" wire:model="department_id">
                            </div>

                            @error('department_id')
                                <p class="mt-1.5 text-xs text-destructive font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- User peminjam (filtered by department) --}}
                        <div>
                            <label class="{{ $label }}">{{ __('app.user_filtered') }}</label>

                            {{-- Combobox: search + select in one --}}
                            <div
                                x-data="{
                                    open: false,
                                    search: '',
                                    get items() {
                                        const q = this.search.toLowerCase().trim();
                                        const list = ($wire.usersForCombobox || []);
                                        return q ? list.filter(i => i.label.toLowerCase().includes(q) || (i.email && i.email.toLowerCase().includes(q))) : list;
                                    },
                                    select(id, label) {
                                        this.search = label;
                                        $wire.set('borrower_user_id', id);
                                        if (!$wire.borrower_name) $wire.set('borrower_name', label);
                                        this.open = false;
                                    },
                                    clear() {
                                        this.search = '';
                                        $wire.set('borrower_user_id', null);
                                        $wire.set('userSearch', '');
                                    }
                                }"
                                x-init="
                                    $watch('search', val => $wire.set('userSearch', val));
                                    $watch('$wire.department_id', () => { search = ''; });
                                "
                                class="relative"
                                @click.outside="open = false"
                            >
                                <div class="relative">
                                    <input
                                        type="text"
                                        x-model="search"
                                        @focus="if ($wire.department_id) open = true"
                                        @input="if ($wire.department_id) open = true"
                                        @keydown.escape="open = false"
                                        @keydown.enter.prevent="items.length === 1 && select(items[0].id, items[0].label)"
                                        autocomplete="off"
                                        placeholder="{{ __('app.search_user') }}"
                                        :disabled="!$wire.department_id"
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

                                {{-- Dropdown list --}}
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
                                            :class="$wire.borrower_user_id == item.id
                                                ? 'bg-primary text-primary-foreground'
                                                : 'text-foreground hover:bg-muted cursor-pointer'"
                                            class="px-3.5 py-2.5 cursor-pointer transition-colors"
                                        >
                                            <span x-text="item.label" class="font-medium"></span>
                                            <span x-show="item.email" x-text="' — ' + item.email" class="text-xs opacity-60"></span>
                                        </li>
                                    </template>
                                </ul>
                                <p x-show="open && items.length === 0 && search && $wire.department_id" class="absolute z-30 mt-1 w-full rounded-lg border border-border bg-card shadow-lg text-sm px-3.5 py-2.5 text-muted-foreground" style="display:none">
                                    {{ __('app.no_users_found') }}
                                </p>
                                <p x-show="!$wire.department_id" class="mt-1.5 text-[11px] text-muted-foreground">
                                    {{ __('app.select_dept_first') }}
                                </p>

                                {{-- Hidden real input for Livewire --}}
                                <input type="hidden" wire:model="borrower_user_id">
                            </div>

                            <p class="text-[11px] text-muted-foreground mt-1.5">
                                {{ __('app.borrower_name_hint') }}
                            </p>
                            @error('borrower_user_id')
                                <p class="mt-1.5 text-xs text-destructive font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Nama peminjam manual --}}
                        <div>
                            <label class="{{ $label }}">
                                {{ __('app.borrower_name') }} <span class="text-destructive">*</span>
                            </label>
                            <input
                                type="text"
                                wire:model.defer="borrower_name"
                                placeholder="{{ __('app.borrower_name') }}"
                                class="{{ $input }} md:mt-[50px]"
                            >
                            @error('borrower_name')
                                <p class="mt-1.5 text-xs text-destructive font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Kendaraan --}}
                        <div>
                            <label class="{{ $label }}">{{ __('app.vehicle') }} <span class="text-destructive">*</span></label>
                            <div class="relative">
                                <select
                                    wire:model.defer="vehicle_id"
                                    @if(!$hasVehicles) disabled @endif
                                    class="{{ $input }} appearance-none pr-8 disabled:bg-muted disabled:text-muted-foreground"
                                >
                                    @if(!$hasVehicles)
                                        <option value="">{{ __('app.no_vehicle_data') }}</option>
                                    @else
                                        <option value="">{{ __('app.select_vehicle') }}</option>
                                        @foreach($vehicles as $v)
                                            @php
                                                $vehicleLabel = $v->name ?? __('app.vehicle');
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
                            <label class="{{ $label }}">{{ __('app.borrow_date') }} <span class="text-destructive">*</span></label>
                            <input type="date" wire:model.defer="date_from" class="{{ $input }}">
                            @error('date_from')
                                <p class="mt-1.5 text-xs text-destructive font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Tanggal kembali --}}
                        <div>
                            <label class="{{ $label }}">{{ __('app.return_date') }} <span class="text-destructive">*</span></label>
                            <input type="date" wire:model.defer="date_to" class="{{ $input }}">
                            @error('date_to')
                                <p class="mt-1.5 text-xs text-destructive font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Jam mulai --}}
                        <div>
                            <label class="{{ $label }}">{{ __('app.start_time') }} <span class="text-destructive">*</span></label>
                            <input type="time" wire:model.defer="start_time" class="{{ $input }}">
                            @error('start_time')
                                <p class="mt-1.5 text-xs text-destructive font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Jam selesai --}}
                        <div>
                            <label class="{{ $label }}">{{ __('app.end_time') }} <span class="text-destructive">*</span></label>
                            <input type="time" wire:model.defer="end_time" class="{{ $input }}">
                            @error('end_time')
                                <p class="mt-1.5 text-xs text-destructive font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Odd/even --}}
                        <div>
                            <label class="{{ $label }}">{{ __('app.odd_even_area') }}</label>
                            <div class="relative">
                                <select wire:model.defer="odd_even_area" class="{{ $input }} appearance-none pr-8">
                                    <option value="tidak">{{ __('app.not_enter') }}</option>
                                    <option value="ganjil">{{ __('app.odd') }}</option>
                                    <option value="genap">{{ __('app.even') }}</option>
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
                            <label class="{{ $label }}">{{ __('app.purpose_type') }}</label>
                            <div class="relative">
                                <select wire:model.live="purpose_type" class="{{ $input }} appearance-none pr-8">
                                    <option value="">{{ __('app.select_purpose') }}</option>
                                    <option value="dinas">{{ __('app.official') }}</option>
                                    <option value="operasional">{{ __('app.operational') }}</option>
                                    <option value="antar jemput">{{ __('app.pickup_dropoff') }}</option>
                                    <option value="lainnya">{{ __('app.other') }}</option>
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
                            <label class="{{ $label }}">{{ __('app.purpose') }} <span class="text-destructive">*</span></label>
                            <input
                                type="text"
                                wire:model.defer="purpose"
                                placeholder="{{ __('app.purpose_placeholder') }}"
                                class="{{ $input }}"
                            >
                            @error('purpose')
                                <p class="mt-1.5 text-xs text-destructive font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Tujuan --}}
                        <div class="md:col-span-3">
                            <label class="{{ $label }}">{{ __('app.destination') }}</label>
                            <input
                                type="text"
                                wire:model.defer="destination"
                                placeholder="{{ __('app.destination_placeholder') }}"
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
                                    {{ __('app.other_purpose_detail') }} <span class="text-destructive">*</span>
                                </label>
                                <input
                                    type="text"
                                    wire:model.defer="purpose_type_other"
                                    placeholder="{{ __('app.other_purpose_placeholder') }}"
                                    class="{{ $input }}"
                                >
                                <p class="text-[11px] text-muted-foreground mt-1.5 font-medium">
                                    {{ __('app.other_purpose_required') }}
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
                                <span class="text-xs text-muted-foreground font-semibold group-hover:text-primary transition-colors">{{ __('app.terms_agree') }}</span>
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
