<div class="min-h-screen bg-gray-50" wire:poll.1000ms.keep-alive>
    @php
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Storage;

    if (!function_exists('fmtDate')) {
        function fmtDate($v)
        {
            try {
                return $v ? Carbon::parse($v)->format('d M Y') : '—';
            } catch (\Throwable) {
                return '—';
            }
        }
    }

    if (!function_exists('fmtTime')) {
        function fmtTime($v)
        {
            try {
                return $v ? Carbon::parse($v)->format('H.i') : '—';
            } catch (\Throwable) {
                if (is_string($v)) {
                    if (preg_match('/^\d{2}:\d{2}/', $v))
                        return str_replace(':', '.', substr($v, 0, 5));
                    if (preg_match('/^\d{2}\.\d{2}/', $v))
                        return substr($v, 0, 5);
                }
                return '—';
            }
        }
    }

    $card = 'bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden';
    $label = 'block text-sm font-medium text-gray-700 mb-2';
    $input = 'w-full h-10 px-3 rounded-lg border border-gray-300 text-gray-800 placeholder:text-gray-400 focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10 bg-white transition';
    $chip = 'inline-flex items-center gap-2 px-2.5 py-1 rounded-lg bg-gray-100 text-xs';
    $icoAvatar = 'w-10 h-10 bg-gray-900 rounded-xl flex items-center justify-center text-white font-semibold text-sm shrink-0';
    @endphp

    <main class="px-4 sm:px-6 py-6 space-y-6">
        {{-- HERO --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-gray-900 to-black text-white shadow-2xl">
            <div class="pointer-events-none absolute inset-0 opacity-10">
                <div class="absolute top-0 -right-4 w-24 h-24 bg-white rounded-full blur-xl"></div>
                <div class="absolute bottom-0 -left-4 w-16 h-16 bg-white rounded-full blur-lg"></div>
            </div>
            <div class="relative z-10 p-6 sm:p-8">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div
                            class="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm border border-white/20">
                            <x-heroicon-o-archive-box class="w-6 h-6 text-white"/>
                        </div>
                        <div>
                            <h2 class="text-lg sm:text-xl font-semibold">Documents & Packages — Status</h2>
                            <p class="text-sm text-white/80">Pantau item pending & tersimpan sebelum delivered/taken.
                            </p>
                        </div>
                    </div>

                    {{-- MOBILE FILTER BUTTON --}}
                    <button type="button"
                        class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-white/10 text-xs font-medium border border-white/30 hover:bg-white/20 md:hidden"
                        wire:click="openFilterModal">
                        <x-heroicon-o-bars-3 class="w-4 h-4"/>
                        <span>Filter</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- MAIN LAYOUT: LEFT (ITEMS LIST) + RIGHT (SIDEBAR) --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            {{-- LEFT: ITEMS LIST CARD --}}
            <section class="{{ $card }} md:col-span-3">
                {{-- Header: title + tabs + type scope --}}
                <div class="px-4 sm:px-6 pt-4 pb-3 border-b border-gray-200 space-y-3">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900">Items</h3>
                            <p class="text-xs text-gray-500">Daftar dokumen & paket yang diterima.</p>
                        </div>

                        {{-- Tabs: Pending / Stored --}}
                        <div class="inline-flex items-center bg-gray-100 rounded-full p-1 text-xs font-medium">
                            <button type="button" wire:click="setTab('pending')" class="px-3 py-1 rounded-full transition
                                        {{ $activeTab === 'pending'
    ? 'bg-gray-900 text-white shadow-sm'
    : 'text-gray-700 hover:bg-gray-200' }}">
                                Pending
                            </button>
                            <button type="button" wire:click="setTab('stored')" class="px-3 py-1 rounded-full transition
                                        {{ $activeTab === 'stored'
    ? 'bg-gray-900 text-white shadow-sm'
    : 'text-gray-700 hover:bg-gray-200' }}">
                                Stored
                            </button>
                        </div>
                    </div>

                    {{-- Type scope: All / Document / Package --}}
                    <div class="flex justify-end">
                        <div class="inline-flex items-center bg-gray-100 rounded-full p-1 text-[11px] font-medium">
                            <button type="button" wire:click="$set('type', 'all')" class="px-3 py-1 rounded-full transition
                                        {{ $type === 'all'
    ? 'bg-gray-900 text-white shadow-sm'
    : 'text-gray-700 hover:bg-gray-200' }}">
                                All
                            </button>
                            <button type="button" wire:click="$set('type', 'document')" class="px-3 py-1 rounded-full transition
                                        {{ $type === 'document'
    ? 'bg-gray-900 text-white shadow-sm'
    : 'text-gray-700 hover:bg-gray-200' }}">
                                Document
                            </button>
                            <button type="button" wire:click="$set('type', 'package')" class="px-3 py-1 rounded-full transition
                                        {{ $type === 'package'
    ? 'bg-gray-900 text-white shadow-sm'
    : 'text-gray-700 hover:bg-gray-200' }}">
                                Package
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Filters (search, date, order) --}}
                <div class="px-4 sm:px-6 pt-4 pb-3 border-b border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="{{ $label }}">Search</label>
                            <div class="relative">
                                <input type="text" class="{{ $input }} pl-9"
                                    placeholder="Cari nama item / pengirim / penerima…" wire:model.live="q">
                                <x-heroicon-o-magnifying-glass class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"/>
                            </div>
                        </div>
                        <div>
                            <label class="{{ $label }}">Tanggal</label>
                            <div class="relative">
                                <input type="date" class="{{ $input }} pl-9" wire:model.live="selectedDate">
                                <x-heroicon-o-calendar-days class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"/>
                            </div>
                        </div>
                        <div>
                            <label class="{{ $label }}">Urutkan</label>
                            <select class="{{ $input }}" wire:model.live="dateMode">
                                <option value="semua">Default (terbaru)</option>
                                <option value="terbaru">Terbaru</option>
                                <option value="terlama">Terlama</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- LIST (GRID LAYOUT) --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 p-4 bg-gray-50/50">
                    
                    {{-- PENDING TAB (MODIFIED) --}}
                    @if($activeTab === 'pending')
                        @forelse($pending as $row)
                            @php
                                $avatarChar = strtoupper(substr($row->item_name ?? 'P', 0, 1));
                                $rowNo = ($pending->firstItem() ?? 1) + $loop->index;
                            @endphp

                            {{-- START: MODIFIED PENDING CARD DESIGN --}}
                            <div wire:key="pend-{{ $row->delivery_id }}"
                                class="bg-white border border-gray-200 rounded-xl p-4 space-y-3 hover:shadow-sm hover:border-gray-300 transition-all duration-200">
                                
                                <div class="flex items-start gap-4">
                                    {{-- 1. Avatar/Image on the left --}}
                                    <div class="{{ $icoAvatar }} mt-0.5">
                                        @if($row->image)
                                            <img src="{{ Storage::disk('public')->url($row->image) }}" alt="Bukti foto"
                                                class="w-full h-full object-cover rounded-xl">
                                        @else
                                            {{ $avatarChar }}
                                        @endif
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        {{-- 2. TOP ROW: Title, Type, Status --}}
                                        <div class="flex items-center justify-between gap-3 min-w-0 mb-2">
                                            <h4 class="font-semibold text-gray-900 text-base truncate pr-2 max-w-full">
                                                {{ $row->item_name }}
                                            </h4>
                                            <div class="flex-shrink-0 flex items-center gap-2">
                                                {{-- Type Chip --}}
                                                <span class="text-[11px] px-2 py-0.5 rounded border border-gray-300 text-gray-600 bg-gray-50 flex-shrink-0">
                                                    {{ strtoupper($row->type) }}
                                                </span>
                                                {{-- Status Chip --}}
                                                <span class="text-[11px] px-2 py-0.5 rounded bg-amber-100 text-amber-800 font-medium flex-shrink-0">
                                                    Pending
                                                </span>
                                            </div>
                                        </div>

                                        {{-- 3. MIDDLE SECTION: Sender, Receiver, Received Date --}}
                                        <div class="space-y-2 text-[13px] text-gray-600 mb-3 border-y border-gray-100 py-2">
                                            @if($row->nama_pengirim)
                                                <div class="flex items-center gap-2">
                                                    <x-heroicon-o-user class="w-4 h-4 text-gray-400"/>
                                                    <span class="truncate font-medium text-gray-800">From: {{ $row->nama_pengirim }}</span>
                                                </div>
                                            @endif
                                            @if($row->nama_penerima)
                                                <div class="flex items-center gap-2">
                                                    <x-heroicon-o-user class="w-4 h-4 text-gray-400"/>
                                                    <span class="truncate font-medium text-gray-800">To: {{ $row->nama_penerima }}</span>
                                                </div>
                                            @endif
                                            @if($row->created_at)
                                                <div class="flex items-center gap-2 text-[12px] text-gray-600">
                                                    <x-heroicon-o-clock class="w-3.5 h-3.5 text-gray-400"/>
                                                    <span>Received: {{ fmtDate($row->created_at) }} {{ fmtTime($row->created_at) }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                {{-- 4. BOTTOM ACTIONS --}}
                                <div class="pt-3 mt-auto border-t border-gray-100 flex items-center justify-end gap-3">
                                    <span class="text-[11px] text-gray-500 mr-auto">
                                        #{{ $rowNo }}
                                    </span>
                                    
                                    <button type="button" wire:click="openEdit({{ $row->delivery_id }})"
                                        wire:loading.attr="disabled"
                                        class="px-4 py-2 text-xs font-medium rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500/20 transition">
                                        Edit
                                    </button>
                                    <button type="button" wire:click="storeItem({{ $row->delivery_id }})"
                                        wire:loading.attr="disabled"
                                        class="px-4 py-2 text-xs font-medium rounded-lg bg-gray-900 text-white hover:bg-black focus:outline-none focus:ring-2 focus:ring-gray-900/20 shadow-sm transition">
                                        Store
                                    </button>
                                </div>
                            </div>
                            {{-- END: MODIFIED PENDING CARD DESIGN --}}
                        @empty
                            <div class="col-span-full px-4 py-14 text-center text-gray-500 text-sm">Tidak ada data</div>
                        @endforelse
                    @endif

                    {{-- STORED TAB (MODIFIED) --}}
                    @if($activeTab === 'stored')
                        @forelse($stored as $row)
                            @php
                                $avatarChar = strtoupper(substr($row->item_name ?? 'S', 0, 1));
                                $rowNo = ($stored->firstItem() ?? 1) + $loop->index;
                                $dir = $storedDirections[$row->delivery_id] ?? 'taken';
                                $dirLabel = $dir === 'deliver' ? 'Deliver' : 'Taken';
                                $actionLabel = $dir === 'deliver' ? 'Delivered' : 'Taken';
                            @endphp

                            {{-- START: MODIFIED STORED CARD DESIGN --}}
                            <div wire:key="stor-{{ $row->delivery_id }}"
                                class="bg-white border border-gray-200 rounded-xl p-4 space-y-3 hover:shadow-sm hover:border-gray-300 transition-all duration-200">
                                
                                <div class="flex items-start gap-4">
                                    {{-- 1. Avatar/Image on the left --}}
                                    <div class="{{ $icoAvatar }} mt-0.5">
                                        @if($row->image)
                                            <img src="{{ Storage::disk('public')->url($row->image) }}" alt="Bukti foto"
                                                class="w-full h-full object-cover rounded-xl">
                                        @else
                                            {{ $avatarChar }}
                                        @endif
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        {{-- 2. TOP ROW: Title, Type, Status --}}
                                        <div class="flex items-center justify-between gap-3 min-w-0 mb-2">
                                            <h4 class="font-semibold text-gray-900 text-base truncate pr-2 max-w-full">
                                                {{ $row->item_name }}
                                            </h4>
                                            <div class="flex-shrink-0 flex items-center gap-2">
                                                {{-- Type Chip --}}
                                                <span class="text-[11px] px-2 py-0.5 rounded border border-gray-300 text-gray-600 bg-gray-50 flex-shrink-0">
                                                    {{ strtoupper($row->type) }}
                                                </span>
                                                {{-- Status Chip --}}
                                                <span class="text-[11px] px-2 py-0.5 rounded bg-blue-100 text-blue-800 font-medium flex-shrink-0">
                                                    Stored
                                                </span>
                                            </div>
                                        </div>

                                        {{-- 3. MIDDLE SECTION: Sender, Receiver, Direction --}}
                                        <div class="space-y-2 text-[13px] text-gray-600 mb-3 border-y border-gray-100 py-2">
                                            @if($row->nama_pengirim)
                                                <div class="flex items-center gap-2">
                                                    <x-heroicon-o-user class="w-4 h-4 text-gray-400"/>
                                                    <span class="truncate font-medium text-gray-800">From: {{ $row->nama_pengirim }}</span>
                                                </div>
                                            @endif
                                            @if($row->nama_penerima)
                                                <div class="flex items-center gap-2">
                                                    <x-heroicon-o-user class="w-4 h-4 text-gray-400"/>
                                                    <span class="truncate font-medium text-gray-800">To: {{ $row->nama_penerima }}</span>
                                                </div>
                                            @endif
                                            <div class="flex items-center gap-2 text-[12px] text-gray-600">
                                                <x-heroicon-o-arrow-up-right class="w-3.5 h-3.5 text-gray-400"/>
                                                <span>Direction: {{ $dirLabel }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- 4. BOTTOM ACTIONS --}}
                                <div class="pt-3 mt-auto border-t border-gray-100 flex items-center justify-end gap-3">
                                    <span class="text-[11px] text-gray-500 mr-auto">
                                        #{{ $rowNo }}
                                    </span>
                                    
                                    <button type="button" wire:click="openEdit({{ $row->delivery_id }})"
                                        wire:loading.attr="disabled"
                                        class="px-4 py-2 text-xs font-medium rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500/20 transition">
                                        Edit
                                    </button>
                                    <button type="button" wire:click="finalizeItem({{ $row->delivery_id }})"
                                        wire:loading.attr="disabled"
                                        class="px-4 py-2 text-xs font-medium rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-600/20 shadow-sm transition">
                                        {{ $actionLabel }}
                                    </button>
                                </div>
                            </div>
                            {{-- END: MODIFIED STORED CARD DESIGN --}}
                        @empty
                            <div class="col-span-full px-4 py-14 text-center text-gray-500 text-sm">Tidak ada data</div>
                        @endforelse
                    @endif
                </div>

                {{-- Pagination --}}
                <div class="px-4 sm:px-6 py-5 bg-white border-t border-gray-200 rounded-b-2xl">
                    <div class="flex justify-center">
                        @if($activeTab === 'pending')
                            {{ $pending->onEachSide(1)->links() }}
                        @else
                            {{ $stored->onEachSide(1)->links() }}
                        @endif
                    </div>
                </div>
            </section>

            {{-- RIGHT: SIDEBAR (DESKTOP / TABLET) --}}
            <aside class="hidden md:flex md:flex-col md:col-span-1 gap-4">
                {{-- Filter by Department & User --}}
                <section class="{{ $card }}">
                    <div class="px-4 py-4 border-b border-gray-200">
                        <h3 class="text-sm font-semibold text-gray-900">Advanced Filters</h3>
                        <p class="text-xs text-gray-500 mt-1">Filter berdasarkan department & user.</p>
                    </div>

                    <div class="px-4 py-3 space-y-4">
                        {{-- Department Filter --}}
                        <div>
                            <label class="{{ $label }}">Department</label>
                            <input type="text" wire:model.live="departmentQ" class="{{ $input }}"
                                placeholder="Cari department...">
                            <select wire:model.live="departmentId" class="{{ $input }} mt-2">
                                <option value="">Semua Department</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->department_id }}">{{ $dept->department_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- User Filter --}}
                        <div>
                            <label class="{{ $label }}">Receptionist / User</label>
                            <input type="text" wire:model.live="userQ" class="{{ $input }}" placeholder="Cari user...">
                            <select wire:model.live="userId" class="{{ $input }} mt-2">
                                <option value="">Semua User</option>
                                @foreach($users as $u)
                                    <option value="{{ $u->user_id }}">{{ $u->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </section>
            </aside>
        </div>
    </main>

    {{-- MOBILE FILTER MODAL --}}
    @if($showFilterModal)
        <div class="fixed inset-0 z-40 md:hidden">
            <div class="absolute inset-0 bg-black/40" wire:click="closeFilterModal"></div>
            <div class="absolute inset-x-0 bottom-0 bg-white rounded-t-2xl shadow-xl max-h-[80vh] overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">Advanced Filters</h3>
                        <p class="text-[11px] text-gray-500">Filter berdasarkan department & user.</p>
                    </div>
                    <button type="button" class="text-gray-500 hover:text-gray-700" wire:click="closeFilterModal">
                        <x-heroicon-o-x-mark class="w-5 h-5"/>
                    </button>
                </div>

                <div class="p-4 space-y-4 max-h-[70vh] overflow-y-auto">
                    {{-- Department Filter --}}
                    <div>
                        <label class="{{ $label }}">Department</label>
                        <input type="text" wire:model.live="departmentQ" class="{{ $input }}"
                            placeholder="Cari department...">
                        <select wire:model.live="departmentId" class="{{ $input }} mt-2">
                            <option value="">Semua Department</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->department_id }}">{{ $dept->department_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- User Filter --}}
                    <div>
                        <label class="{{ $label }}">Receptionist / User</label>
                        <input type="text" wire:model.live="userQ" class="{{ $input }}" placeholder="Cari user...">
                        <select wire:model.live="userId" class="{{ $input }} mt-2">
                            <option value="">Semua User</option>
                            @foreach($users as $u)
                                <option value="{{ $u->user_id }}">{{ $u->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="px-4 py-3 border-t border-gray-200">
                    <button type="button" class="w-full h-10 rounded-xl bg-gray-900 text-white text-xs font-medium"
                        wire:click="closeFilterModal">
                        Apply & Close
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- EDIT MODAL --}}
    @if($showEdit)
        <div class="fixed inset-0 z-50">
            <div class="absolute inset-0 bg-black/50"></div>
            <div class="absolute inset-0 flex items-center justify-center p-4">
                <div class="w-full max-w-lg bg-white rounded-2xl border border-gray-200 shadow-lg overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                        <h3 class="font-semibold text-black">Edit Item</h3>
                        <button type="button" class="text-gray-500 hover:text-gray-700"
                            wire:click="$set('showEdit', false)">
                            <x-heroicon-o-x-mark class="w-5 h-5"/>
                        </button>
                    </div>
                    <div class="p-5 space-y-4">
                        <div>
                            <label class="{{ $label }}">Item Name</label>
                            <input type="text" class="{{ $input }}" wire:model.defer="edit.item_name">
                            @error('edit.item_name') <p class="text-sm text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="{{ $label }}">Nama Pengirim</label>
                                <input type="text" class="{{ $input }}" wire:model.defer="edit.nama_pengirim">
                                @error('edit.nama_pengirim') <p class="text-sm text-rose-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="{{ $label }}">Nama Penerima</label>
                                <input type="text" class="{{ $input }}" wire:model.defer="edit.nama_penerima">
                                @error('edit.nama_penerima') <p class="text-sm text-rose-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="px-5 py-4 border-t border-gray-200 flex items-center justify-end gap-2">
                        <button type="button" wire:click="$set('showEdit', false)"
                            class="h-10 px-4 rounded-xl bg-gray-200 text-gray-900 text-sm font-medium hover:bg-gray-300 focus:outline-none">
                            Batal
                        </button>
                        <button type="button" wire:click="saveEdit" wire:loading.attr="disabled" wire:target="saveEdit"
                            class="h-10 px-4 rounded-xl bg-gray-900 text-white text-sm font-medium hover:bg-black focus:outline-none focus:ring-2 focus:ring-gray-900/20 disabled:opacity-60 transition shadow-sm">
                            <span wire:loading.remove wire:target="saveEdit">Simpan Perubahan</span>
                            <span wire:loading wire:target="saveEdit" class="flex items-center gap-2">
                                <x-heroicon-o-arrow-path class="animate-spin -ml-1 mr-1 h-4 w-4 text-white"/>
                                Menyimpan…
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>