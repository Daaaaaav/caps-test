<div class="bg-gray-50 min-h-screen" wire:key="information-root">
    @php
    // LAYOUT HELPERS (Harmonized with Ticket Style)
    $card = 'bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden';
    $label = 'block text-sm font-medium text-gray-700 mb-2';
    $input = 'w-full h-10 px-3 rounded-lg border border-gray-300 text-gray-800 placeholder:text-gray-400 focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10 bg-white transition';

    // BUTTONS (Harmonized with Ticket Style)
    $btnBlk = 'px-3 py-2 text-xs font-medium rounded-lg bg-gray-900 text-white hover:bg-black focus:outline-none focus:ring-2 focus:ring-gray-900/20 disabled:opacity-60 transition inline-flex items-center justify-center';
    $btnLt = 'px-3 py-2 text-xs font-medium rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-300/40 disabled:opacity-60 transition inline-flex items-center justify-center';
    $btnIcon = 'p-2 rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-900 transition focus:outline-none focus:ring-2 focus:ring-gray-200';

    // BADGES & ICONS (Harmonized with Ticket Style)
    // Base chip style
    $chip = 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium ring-1 ring-inset';
    // Specific chips for clarity/consistency
    $chipInfo = 'bg-gray-100 text-gray-700 ring-gray-200';
    $chipStatus = [
    'APPROVED' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
    'REQUEST' => 'bg-amber-50 text-amber-700 ring-amber-200',
    'REJECTED' => 'bg-rose-50 text-rose-700 ring-rose-200',
    'CANCELLED' => 'bg-gray-100 text-gray-700 ring-gray-200',
    ];
    $mono = 'text-[10px] font-mono text-gray-500 bg-gray-100 px-2.5 py-1 rounded-md';
    $ico = 'w-10 h-10 bg-gray-900 rounded-xl flex items-center justify-center text-white font-semibold text-sm shrink-0';

    // Custom Fix: A fixed-height box is no longer needed but retaining the styling for consistency
    $detailBox = 'mt-3 w-full bg-gray-50 border border-gray-100 rounded-lg p-3 flex flex-col justify-center text-xs text-gray-700';
    @endphp

    <main class="px-4 sm:px-6 py-6 space-y-8">
        {{-- HERO --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-gray-900 to-black text-white shadow-2xl">
            <div class="pointer-events-none absolute inset-0 opacity-10">
                <div class="absolute top-0 -right-6 w-28 h-28 bg-white/20 rounded-full blur-xl"></div>
                <div class="absolute bottom-0 -left-6 w-20 h-20 bg-white/10 rounded-full blur-lg"></div>
            </div>

            <div class="relative z-10 p-6 sm:p-8">
                <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                    {{-- LEFT SECTION --}}
                    <div class="flex items-start gap-4 sm:gap-6">
                        <div class="w-12 h-12 sm:w-14 sm:h-14 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm border border-white/20 shrink-0">
                            <x-heroicon-o-information-circle class="w-6 h-6 text-white" />
                        </div>

                        <div class="space-y-1.5">
                            <h2 class="text-xl sm:text-2xl font-semibold leading-tight">
                                Information Center
                            </h2>

                            <div class="text-sm text-white/80 flex flex-col sm:block">
                                <span>Cabang: <span class="font-semibold">{{ $company_name }}</span></span>
                                <span class="hidden sm:inline mx-2">•</span>
                                <span>Departemen: <span class="font-semibold">{{ $department_name }}</span></span>
                            </div>

                            <p class="text-xs text-white/60 pt-1 sm:pt-0">
                                Menampilkan informasi dan notifikasi untuk departemen:
                                <span class="font-medium">{{ $department_name }}</span>.
                            </p>
                        </div>
                    </div>

                    {{-- RIGHT SECTION --}}
                    @if ($showSwitcher)
                    <div class="w-full lg:w-[32rem] lg:ml-6">
                        <label class="block text-xs font-medium text-white/80 mb-2">
                            Pilih Departemen
                        </label>
                        <select
                            wire:model.live="selected_department_id"
                            class="w-full h-11 sm:h-12 px-3 sm:px-4 rounded-lg border border-white/20 bg-white/10 text-white text-sm placeholder:text-white/60 focus:border-white focus:ring-2 focus:ring-white/30 focus:outline-none transition">
                            <option class="text-gray-900" value="{{ auth()->user()->department_id }}">
                                {{ auth()->user()->department->name }} (Your Primary Department)
                            </option>
                            @foreach ($deptOptions as $opt)
                            <option class="text-gray-900" value="{{ $opt['id'] }}">
                                {{ $opt['name'] }}{{ $opt['id'] === $primary_department_id ? ' — Primary' : '' }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    @else
                    {{-- SEARCH VERSION --}}
                    <div class="w-full lg:w-80 lg:ml-auto">
                        <label class="sr-only">Search</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 pl-3 flex items-center">
                                <x-heroicon-o-magnifying-glass class="w-4 h-4 text-gray-300" />
                            </span>
                            <input
                                type="text"
                                wire:model.live.debounce.400ms="search"
                                placeholder="Cari deskripsi atau catatan…"
                                class="w-full h-11 pl-9 pr-3 sm:pl-9 sm:pr-3.5 bg-white/10 border border-white/20 rounded-lg text-sm placeholder:text-gray-300 focus:outline-none focus:ring-2 focus:ring-white/30 focus:border-white transition">
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- REQUEST QUEUE --}}
        <section class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- OFFLINE BOOKINGS - MODIFIED TO USE CARDS --}}
            <div class="{{ $card }} flex flex-col">
                <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between shrink-0">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-building-office-2 class="w-5 h-5 text-gray-700" />
                        <div>
                            <h3 class="text-base font-semibold text-gray-900">Offline Bookings</h3>
                            <p class="text-xs text-gray-500">Approved &amp; Request ({{ $offline->total() }})</p>
                        </div>
                    </div>
                </div>

                {{-- Changed from divide-y to use spacing (space-y-4) and padding (p-4) --}}
                <div class="p-4 space-y-4 flex-1">
                    @forelse ($offline as $b)
                    @php
                    $title = $b->meeting_title ?: 'Meeting';
                    $date = \Carbon\Carbon::parse($b->date)->translatedFormat('d M Y');
                    $start = \Carbon\Carbon::parse($b->start_time)->format('H:i');
                    $end = \Carbon\Carbon::parse($b->end_time)->format('H:i');
                    $room = optional($b->room)->name ?? '-';
                    $status = strtoupper($b->status ?? 'REQUEST');
                    $needInform = ($b->requestinformation ?? null) === 'request';
                    $statusChipClass = $chipStatus[$status] ?? $chipInfo;
                    @endphp

                    {{-- Card Structure --}}
                    <div class="p-4 bg-white rounded-lg border border-gray-100 shadow-sm hover:border-gray-300 transition-all" wire:key="off-{{ $b->bookingroom_id }}">
                        <div class="flex items-start gap-4">

                            {{-- Left Side: Icon --}}
                            <div>

                                <div class="{{ $mono }} mt-1">#{{ $loop->iteration }}</div>
                                <div class="{{ $ico }} mt-3">OF</div>
                            </div>

                            <div class="flex-1 min-w-0">

                                {{-- Top: Title & Status --}}
                                <div>
                                    <div class="flex items-center gap-3 mb-2">
                                        <h4 class="font-bold text-gray-900 text-base truncate max-w-[200px] sm:max-w-xs">
                                            {{ $title }}
                                        </h4>
                                        {{-- Status Chip --}}
                                        <span class="{{ $chip }} {{ $statusChipClass }} ml-auto">
                                            <x-heroicon-o-check-badge class="w-3.5 h-3.5" />
                                            <span class="font-medium">{{ $status }}</span>
                                        </span>
                                    </div>

                                    {{-- Grouped Metadata Box --}}
                                    <div class="bg-gray-50 rounded-md p-2 flex flex-col sm:flex-row sm:items-center gap-2 text-xs text-gray-700 mb-3 border border-gray-100">
                                        {{-- Room --}}
                                        <div class="flex items-center gap-1.5 font-medium">
                                            <x-heroicon-o-rectangle-stack class="w-3.5 h-3.5 text-gray-500 shrink-0" />
                                            <span class="text-gray-900">{{ $room }}</span>
                                        </div>
                                        <span class="hidden sm:inline text-gray-300">|</span>
                                        {{-- Date & Time --}}
                                        <p class="flex items-center gap-1.5 text-gray-600">
                                            <x-heroicon-o-calendar class="w-3.5 h-3.5 shrink-0" />
                                            {{ $date }}
                                            <span class="font-medium text-gray-900 ml-1">{{ $start }} - {{ $end }}</span>
                                        </p>
                                    </div>
                                </div>

                                {{-- Bottom: Detail Box (Notes) --}}
                                <div class="{{ $detailBox }} !mt-0 p-3">
                                    @if (!empty($b->special_notes))
                                    <div class="space-y-1">
                                        <p class="font-medium text-gray-900">Notes:</p>
                                        <p class="break-words leading-relaxed line-clamp-2">{{ $b->special_notes }}</p>
                                    </div>
                                    @else
                                    <p class="text-gray-400 italic">No additional notes</p>
                                    @endif
                                </div>

                                {{-- Actions --}}
                                <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                                    {{-- MODIFIED: Show sequential count --}}

                                    <div class="flex gap-2">
                                        @if ($needInform)
                                        <button type="button"
                                            class="{{ $btnBlk }} inline-flex items-center gap-1.5"
                                            wire:click.prevent="openInformModal({{ $b->bookingroom_id }})">
                                            <x-heroicon-o-paper-airplane class="w-4 h-4" />
                                            <span>Inform</span>
                                        </button>
                                        <button type="button"
                                            class="{{ $btnBlk }} inline-flex items-center gap-1.5 bg-rose-600 hover:bg-rose-700 focus:ring-rose-900/20"
                                            wire:click.prevent="openRejectModal({{ $b->bookingroom_id }})">
                                            <x-heroicon-o-x-circle class="w-4 h-4" />
                                            <span>Reject</span>
                                        </button>
                                        @else
                                        <div class="text-xs text-gray-600 py-2">
                                            {{ $b->requestinformation ?? '-' }}
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="px-5 py-14 text-center text-gray-500 text-sm">
                        Tidak ada data.
                    </div>
                    @endforelse
                </div>

                @if ($offline->hasPages())
                <div class="px-5 py-4 bg-gray-50 border-t border-gray-200 shrink-0">
                    <div class="flex justify-center">
                        {{ $offline->onEachSide(1)->links() }}
                    </div>
                </div>
                @endif
            </div>

            {{-- ONLINE BOOKINGS - MODIFIED TO USE CARDS --}}
            <div class="{{ $card }} flex flex-col">
                <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between shrink-0">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-wifi class="w-5 h-5 text-gray-700" />
                        <div>
                            <h3 class="text-base font-semibold text-gray-900">Online Bookings</h3>
                            <p class="text-xs text-gray-500">Approved &amp; Request ({{ $online->total() }})</p>
                        </div>
                    </div>
                </div>

                {{-- Changed from divide-y to use spacing (space-y-4) and padding (p-4) --}}
                <div class="p-4 space-y-4 flex-1">
                    @forelse ($online as $b)
                    @php
                    $title = $b->meeting_title ?: 'Online Meeting';
                    $date = \Carbon\Carbon::parse($b->date)->translatedFormat('d M Y');
                    $start = \Carbon\Carbon::parse($b->start_time)->format('H:i');
                    $end = \Carbon\Carbon::parse($b->end_time)->format('H:i');
                    $provider = strtoupper((string) ($b->online_provider ?? '-'));
                    $code = $b->online_meeting_code ?: '-';
                    $url = $b->online_meeting_url ?: null;
                    $pass = $b->online_meeting_password ?: '-';
                    $status = strtoupper($b->status ?? 'REQUEST');
                    $needInform = ($b->requestinformation ?? null) === 'request';
                    $statusChipClass = $chipStatus[$status] ?? $chipInfo;
                    @endphp

                    {{-- Card Structure --}}
                    <div class="p-4 bg-white rounded-lg border border-gray-100 shadow-sm hover:border-gray-300 transition-all" wire:key="on-{{ $b->bookingroom_id }}">
                        <div class="flex items-start gap-4">

                            {{-- Left Side --}}
                            <div>
                                <div class="{{ $mono }}mt-1">#{{ $loop->iteration }}</div>
                                <div class="{{ $ico }} mt-3">ON</div>
                            </div>

                            <div class="flex-1 min-w-0">

                                {{-- Top: Title & Status --}}
                                <div>
                                    <div class="flex items-center gap-3 mb-2">
                                        <h4 class="font-bold text-gray-900 text-base truncate max-w-[200px] sm:max-w-xs">
                                            {{ $title }}
                                        </h4>
                                        {{-- Status Chip --}}
                                        <span class="{{ $chip }} {{ $statusChipClass }} ml-auto">
                                            <x-heroicon-o-check-badge class="w-3.5 h-3.5" />
                                            <span class="font-medium">{{ $status }}</span>
                                        </span>
                                    </div>

                                    {{-- Grouped Metadata Box --}}
                                    <div class="bg-gray-50 rounded-md p-2 flex flex-col sm:flex-row sm:items-center gap-2 text-xs text-gray-700 mb-3 border border-gray-100">
                                        {{-- Provider --}}
                                        <div class="flex items-center gap-1.5 font-medium">
                                            <x-heroicon-o-swatch class="w-3.5 h-3.5 text-gray-500 shrink-0" />
                                            <span class="text-gray-900">{{ $provider }}</span>
                                        </div>
                                        <span class="hidden sm:inline text-gray-300">|</span>
                                        {{-- Date & Time --}}
                                        <p class="flex items-center gap-1.5 text-gray-600">
                                            <x-heroicon-o-calendar class="w-3.5 h-3.5 shrink-0" />
                                            {{ $date }}
                                            <span class="font-medium text-gray-900 ml-1">{{ $start }} - {{ $end }}</span>
                                        </p>
                                    </div>
                                </div>

                                {{-- Bottom: Detail Box (Online Links/Codes) --}}
                                <div class="{{ $detailBox }} !mt-0 p-3">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-1 gap-x-4 w-full">
                                        <div class="truncate">Kode: <span class="font-medium text-gray-900">{{ $code }}</span></div>
                                        <div class="truncate">Pass: <span class="font-medium text-gray-900">{{ $pass }}</span></div>
                                        <div class="col-span-1 sm:col-span-2 break-all flex gap-1 items-start">
                                            <span class="shrink-0">Link:</span>
                                            @if (!empty($url))
                                            <a href="{{ $url }}" target="_blank" class="underline text-blue-600 hover:text-blue-800 text-ellipsis overflow-hidden">
                                                {{ Str::limit($url, 60) }}
                                            </a>
                                            @else
                                            <span>-</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                {{-- Actions --}}
                                <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                                    {{-- MODIFIED: Show sequential count --}}
                                    <div class="flex gap-2">
                                        @if ($needInform)
                                        <button type="button"
                                            class="{{ $btnBlk }} inline-flex items-center gap-1.5"
                                            wire:click.prevent="openInformModal({{ $b->bookingroom_id }})">
                                            <x-heroicon-o-paper-airplane class="w-4 h-4" />
                                            <span>Inform</span>
                                        </button>
                                        <button type="button"
                                            class="{{ $btnBlk }} inline-flex items-center gap-1.5 bg-rose-600 hover:bg-rose-700 focus:ring-rose-900/20"
                                            wire:click.prevent="openRejectModal({{ $b->bookingroom_id }})">
                                            <x-heroicon-o-x-circle class="w-4 h-4" />
                                            <span>Reject</span>
                                        </button>
                                        @else
                                        <div class="text-xs text-gray-600 py-2">
                                            {{ $b->requestinformation ?? '-' }}
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="px-5 py-14 text-center text-gray-500 text-sm">
                        Tidak ada data.
                    </div>
                    @endforelse
                </div>

                @if ($online->hasPages())
                <div class="px-5 py-4 bg-gray-50 border-t border-gray-200 shrink-0">
                    <div class="flex justify-center">
                        {{ $online->onEachSide(1)->links() }}
                    </div>
                </div>
                @endif
            </div>
        </section>

        {{-- INFORMATION SECTION (Index table) - COUNT IS ALREADY CORRECT HERE --}}
        <section class="{{ $card }}">
            <div class="px-5 py-4 border-b border-gray-200 flex flex-col sm:flex-row items-start sm:items-center justify-between bg-white gap-2">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-clipboard-document-list class="w-5 h-5 text-gray-700" />
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">Department Information</h3>
                    </div>
                </div>
                <span class="{{ $chip }} {{ $chipInfo }}">Total: {{ $rows->total() }}</span>
            </div>

            <div class="px-5 py-4 bg-white border-b border-gray-100">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="relative w-full sm:w-72">
                        <x-heroicon-o-magnifying-glass
                            class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" />
                        <input
                            type="text"
                            wire:model.live.debounce.400ms="search"
                            placeholder="Search description..."
                            class="pl-10 {{ $input }}">
                    </div>
                    {{-- Button to open modal for creation --}}
                    <button wire:click="openCreateEditModal('create')" type="button" class="{{ $btnBlk }} inline-flex items-center gap-1.5 shrink-0">
                        <x-heroicon-o-plus class="w-4 h-4" />
                        <span>New Information</span>
                    </button>
                </div>
            </div>

            <div class="p-5 bg-gray-50/50">
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                    @forelse ($rows as $r)
                    @php $rowNumber = $rows->firstItem() + $loop->index; @endphp
                    <div class="bg-gray-50 rounded-xl border border-gray-200 p-4 flex flex-col justify-between hover:shadow-md transition-shadow duration-200 group" wire:key="row-{{ $r->information_id }}">
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                {{-- Sequential Count is here: #1, #2, #3, etc. --}}
                                <span class="{{ $mono }}">#{{ $rowNumber }}</span>
                                @if($r->event_at)
                                <div class="flex items-center gap-1.5 text-xs font-medium text-gray-600 bg-gray-100 px-2 py-1 rounded-md">
                                    <x-heroicon-o-calendar class="w-3.5 h-3.5" />
                                    {{ \Carbon\Carbon::parse($r->event_at)->format('d M Y, H:i') }}
                                </div>
                                @endif
                            </div>
                            <p class="text-sm text-gray-800 leading-relaxed mb-4 line-clamp-4 min-h-[5rem]">
                                {{ $r->description }}
                            </p>
                        </div>
                        <div class="pt-3 mt-2 border-t border-gray-100 flex items-center justify-between">
                            <span class="text-[11px] text-gray-400 flex items-center gap-1">
                                <x-heroicon-o-clock class="w-3 h-3" />
                                {{ optional($r->created_at)->diffForHumans() }}
                            </span>
                            <div class="flex items-center gap-2">
                                {{-- Button to open modal for editing --}}
                                <button wire:click="openCreateEditModal('edit', {{ $r->information_id }})" type="button" class="{{ $btnBlk }} inline-flex items-center gap-1.5" title="Edit">
                                    <x-heroicon-o-pencil-square class="w-4 h-4" />
                                    <span>Edit</span>
                                </button>
                                <button wire:click="destroy({{ $r->information_id }})" onclick="return confirm('Delete this item?')" type="button" class="{{ $btnBlk }} inline-flex items-center gap-1.5 bg-rose-600 hover:bg-rose-700 focus:ring-rose-900/20" title="Delete">
                                    <x-heroicon-o-trash class="w-4 h-4" />
                                    <span>Delete</span>
                                </button>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-span-full py-12 flex flex-col items-center justify-center text-center text-gray-500">
                        <x-heroicon-o-clipboard class="w-12 h-12 text-gray-300 mb-3" />
                        <p class="text-sm">No information found.</p>
                    </div>
                    @endforelse
                </div>
            </div>
            @if ($rows->hasPages())
            <div class="px-5 py-4 bg-white border-t border-gray-200">
                <div class="flex justify-center">{{ $rows->onEachSide(1)->links() }}</div>
            </div>
            @endif
        </section>

        {{-- CREATE / EDIT MODAL --}}
        @if ($mode === 'create' || $mode === 'edit')
        <div class="fixed inset-0 z-[70] flex items-center justify-center" role="dialog" aria-modal="true" wire:key="create-edit-modal" wire:keydown.escape.window="cancel">
            <button type="button" class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-opacity" aria-label="Close overlay" wire:click="cancel"></button>
            <div class="relative w-full max-w-lg mx-4 bg-white rounded-2xl border border-gray-200 shadow-xl overflow-hidden focus:outline-none transform transition-all" tabindex="-1">
                {{-- Modal Header --}}
                <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-pencil-square class="w-5 h-5 text-gray-700" />
                        <h3 class="text-base font-semibold text-gray-900">
                            {{ $mode === 'create' ? 'New Information' : 'Edit Information #'.$editingId }}
                        </h3>
                    </div>
                    <button class="text-gray-500 hover:text-gray-700" type="button" wire:click="cancel" aria-label="Close">
                        <x-heroicon-o-x-mark class="w-5 h-5" />
                    </button>
                </div>

                {{-- Modal Body --}}
                <div class="p-5 space-y-5">
                    <div class="{{ $detailBox }} border-dashed border-gray-200">
                        <p class="text-xs font-medium text-gray-600 mb-1">Target Department:</p>
                        <span class="font-semibold text-gray-900">{{ $department_name }}</span>
                    </div>

                    <div>
                        <label class="{{ $label }}">Description</label>
                        <textarea wire:model.defer="description" rows="5" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm text-gray-800 placeholder:text-gray-400 focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10 transition"></textarea>
                        @error('description') <p class="text-sm text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="{{ $label }}">Event At (Date & Time)</label>
                        <input type="datetime-local" wire:model.defer="event_at" class="{{ $input }}">
                        @error('event_at') <p class="text-sm text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="bg-gray-50 px-5 py-4 flex flex-col sm:flex-row items-center justify-end gap-3 border-t border-gray-200">
                    <button type="button" wire:click="cancel" class="{{ $btnLt }} w-full sm:w-auto inline-flex items-center gap-1.5">
                        <x-heroicon-o-x-mark class="w-4 h-4" />
                        <span>Cancel</span>
                    </button>
                    <button type="button" wire:click="{{ $mode === 'create' ? 'store' : 'update' }}" class="{{ $btnBlk }} w-full sm:w-auto inline-flex items-center gap-1.5" wire:loading.attr="disabled" wire:target="{{ $mode === 'create' ? 'store' : 'update' }}">
                        <span class="inline-flex items-center gap-2" wire:loading.remove wire:target="{{ $mode === 'create' ? 'store' : 'update' }}">
                            <x-heroicon-o-check class="w-4 h-4" />
                            <span>{{ $mode === 'create' ? 'Save Information' : 'Update Information' }}</span>
                        </span>
                        <span class="inline-flex items-center gap-2" wire:loading wire:target="{{ $mode === 'create' ? 'store' : 'update' }}">
                            <x-heroicon-o-arrow-path class="h-4 w-4 animate-spin" />
                            <span>Processing...</span>
                        </span>
                    </button>
                </div>
            </div>
        </div>
        @endif


        {{-- INFORM MODAL (Now Editable) --}}
        @if ($informModal)
        <div class="fixed inset-0 z-[60] flex items-center justify-center" role="dialog" aria-modal="true" wire:key="inform-modal" wire:keydown.escape.window="closeInformModal">
            <button type="button" class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-opacity" aria-label="Close overlay" wire:click="closeInformModal"></button>
            <div class="relative w-full max-w-lg mx-4 bg-white rounded-2xl border border-gray-200 shadow-xl overflow-hidden focus:outline-none transform transition-all" tabindex="-1">
                <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-paper-airplane class="w-5 h-5 text-gray-700" />
                        <h3 class="text-base font-semibold text-gray-900">Inform Department</h3>
                    </div>
                    <button class="text-gray-500 hover:text-gray-700" type="button" wire:click="closeInformModal" aria-label="Close">
                        <x-heroicon-o-x-mark class="w-5 h-5" />
                    </button>
                </div>
                <div class="p-5 space-y-4">
                    <p class="text-sm text-gray-700">
                        Anda akan mengirim notifikasi informasi terkait booking
                        {{-- Shows booking title --}}
                        <span class="font-semibold">"{{ $informBookingTitle ?? 'Request' }}"</span>
                        ke departemen: <span class="font-semibold">{{ $department_name }}</span>.
                        Informasi ini akan muncul di bagian Department Information.
                    </p>

                    {{-- Editable Description Block --}}
                    <div>
                        <label for="informDescription" class="block text-sm font-bold text-gray-900 mb-1">Data yang akan di-Inform (Dapat Diedit):</label>
                        {{-- New Editable textarea bound to informDescription --}}
                        <textarea
                            id="informDescription"
                            wire:model.defer="informDescription"
                            rows="10"
                            class="w-full rounded-lg border border-gray-300 text-sm font-mono text-gray-800 bg-gray-50 p-3 placeholder:text-gray-400 focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10 transition whitespace-pre-wrap"></textarea>
                        @error('informDescription') <p class="text-sm text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex flex-col sm:flex-row items-center justify-end gap-3 border-t border-gray-200 pt-4">
                        <button type="button" class="w-full sm:w-auto inline-flex items-center gap-1.5 {{ $btnLt }}" wire:click="closeInformModal">
                            <x-heroicon-o-x-mark class="w-4 h-4" />
                            <span>Cancel</span>
                        </button>
                        <button type="button" class="w-full sm:w-auto inline-flex items-center gap-1.5 {{ $btnBlk }}" wire:click="submitInform" wire:loading.attr="disabled" wire:target="submitInform">
                            <span class="inline-flex items-center gap-2" wire:loading.remove wire:target="submitInform">
                                <x-heroicon-o-paper-airplane class="w-4 h-4" />
                                <span>Send Information</span>
                            </span>
                            <span class="inline-flex items-center gap-2" wire:loading wire:target="submitInform">
                                <x-heroicon-o-arrow-path class="h-4 w-4 animate-spin" />
                                <span>Sending…</span>
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- REJECT MODAL --}}
        @if ($rejectModal)
        <div class="fixed inset-0 z-[60] flex items-center justify-center" role="dialog" aria-modal="true" wire:key="reject-modal" wire:keydown.escape.window="closeRejectModal">
            <button type="button" class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-opacity" aria-label="Close overlay" wire:click="closeRejectModal"></button>
            <div class="relative w-full max-w-lg mx-4 bg-white rounded-2xl border border-gray-200 shadow-xl overflow-hidden focus:outline-none transform transition-all" tabindex="-1">
                <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-x-circle class="w-5 h-5 text-gray-700" />
                        <h3 class="text-base font-semibold text-gray-900">Reject Booking Request</h3>
                    </div>
                    <button class="text-gray-500 hover:text-gray-700" type="button" wire:click="closeRejectModal" aria-label="Close">
                        <x-heroicon-o-x-mark class="w-5 h-5" />
                    </button>
                </div>
                <div class="p-5 space-y-4">
                    <label for="rejectionReason" class="block text-sm font-medium text-gray-700">Rejection Reason</label>
                    <textarea id="rejectionReason" wire:model="rejectionReason" rows="4" class="w-full rounded-lg border border-gray-300 text-sm text-gray-800 placeholder:text-gray-400 focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10 transition"></textarea>
                    @error('rejectionReason') <p class="text-sm text-rose-600 mt-1">{{ $message }}</p> @enderror

                    <div class="flex flex-col sm:flex-row items-center justify-end gap-3 border-t border-gray-200 pt-4">
                        <button type="button" class="w-full sm:w-auto inline-flex items-center gap-1.5 {{ $btnLt }}" wire:click="closeRejectModal">
                            <x-heroicon-o-x-mark class="w-4 h-4" />
                            <span>Cancel</span>
                        </button>
                        <button type="button" class="w-full sm:w-auto inline-flex items-center gap-1.5 {{ $btnBlk }} bg-rose-600 hover:bg-rose-700 focus:ring-rose-900/20" wire:click="submitReject" wire:loading.attr="disabled" wire:target="submitReject">
                            <span class="inline-flex items-center gap-2" wire:loading.remove wire:target="submitReject">
                                <x-heroicon-o-check class="w-4 h-4" />
                                <span>Submit Rejection</span>
                            </span>
                            <span class="inline-flex items-center gap-2" wire:loading wire:target="submitReject">
                                <x-heroicon-o-arrow-path class="h-4 w-4 animate-spin" />
                                <span>Submitting...</span>
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </main>
</div>