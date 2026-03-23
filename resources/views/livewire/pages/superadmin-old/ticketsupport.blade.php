<div class="bg-gray-50">
    @php
    use Carbon\Carbon;
    $card = 'bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden';
    $label = 'block text-sm font-medium text-gray-700 mb-2';
    $input = 'w-full h-10 px-3 rounded-lg border border-gray-300 text-gray-800 placeholder:text-gray-400 focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10 bg-white transition';
    $btnBlk= 'px-3 py-2 text-xs font-medium rounded-lg bg-gray-900 text-white hover:bg-black focus:outline-none focus:ring-2 focus:ring-gray-900/20 disabled:opacity-60 transition';
    $btnRed= 'px-3 py-2 text-xs font-medium rounded-lg bg-rose-600 text-white hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-600/20 disabled:opacity-60 transition';
    $btnLt = 'px-3 py-2 text-xs font-medium rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-900/10 disabled:opacity-60 transition';
    $chip = 'inline-flex items-center gap-2 px-2.5 py-1 rounded-lg bg-gray-100 text-[11px]';
    $mono = 'text-[10px] font-mono text-gray-500 bg-gray-100 px-2.5 py-1 rounded-md';
    $ico = 'w-10 h-10 bg-gray-900 rounded-xl flex items-center justify-center text-white font-semibold text-sm shrink-0';

    // Status colors
    $statusColors = [
        'OPEN' => 'bg-emerald-50 text-emerald-700 border-emerald-300',
        'IN_PROGRESS' => 'bg-yellow-50 text-yellow-700 border-yellow-300',
        'RESOLVED' => 'bg-blue-50 text-blue-700 border-blue-300',
        'CLOSED' => 'bg-gray-100 text-gray-600 border-gray-300',
    ];
    
    // Departments need to be formatted for options list (as done in the component mount)
    $deptOptionsFormatted = collect($deptLookup)->map(fn($name, $id) => ['id' => $id, 'label' => $name])->values()->all();
    $priorityOptions = ['low', 'medium', 'high', 'urgent'];

    // Helper for initials (used in detail modal)
    $initials = function (?string $fullName): string {
        $fullName = trim($fullName ?? '');
        if ($fullName === '') return 'US';
        $parts = preg_split('/\s+/', $fullName);
        $first = strtoupper(mb_substr($parts[0] ?? 'U', 0, 1));
        $last = strtoupper(mb_substr($parts[count($parts)-1] ?? $parts[0] ?? 'S', 0, 1));
        return $first.$last;
    };
    @endphp

    <main class="px-4 sm:px-6 py-6 space-y-8">
        {{-- HERO --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-gray-900 to-black text-white shadow-2xl">
            <div class="relative p-6 sm:p-8">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center border border-white/20">
                            <x-heroicon-o-calendar-days class="w-6 h-6 text-white" />
                        </div>
                        <div>
                            <h2 class="text-lg sm:text-xl font-semibold">Ticket Support Management</h2>
                            <p class="text-sm text-white/80">
                            Cabang: <span
                                class="font-semibold">{{ optional(Auth::user()->company)->company_name ?? '-' }}</span>
                        </p>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-3 ml-auto">
                        <label class="inline-flex items-center gap-2 text-sm text-white/90">
                            <input type="checkbox"
                                   wire:model.live="showDeleted"
                                   class="rounded border-white/30 bg-white/10 focus:ring-white/40">
                            <span>Show Deleted</span>
                        </label>
                        <a href="#" class="{{ $btnLt }} border-white/30 text-white/90 hover:bg-white/10">Go to Agents</a>
                    </div>
                </div>
            </div>
        </div>

        @if (session()->has('success'))
        <div class="bg-white border border-gray-200 shadow-lg rounded-xl px-4 py-3 text-sm text-gray-800">
            {{ session('success') }}
        </div>
        @endif
        
        {{-- MOBILE FILTER BUTTON (Visible on small screens) --}}
        <div class="md:hidden">
            <div class="flex items-center justify-between gap-3 p-3 bg-white rounded-xl shadow-sm border border-gray-200">
                <p class="text-xs text-gray-600">
                    @if($departmentFilter)
                        Dept: **{{ $deptLookup[$departmentFilter] ?? '—' }}**
                    @elseif($priorityFilter)
                        Priority: **{{ ucfirst($priorityFilter) }}**
                    @else
                        Showing **All** Tickets
                    @endif
                </p>
                <button type="button" class="{{ $btnBlk }} !h-8 !px-4 !py-0" wire:click="openFilterModal">
                    Filter
                </button>
            </div>
        </div>

        {{-- MAIN LAYOUT (Ticket List and Sidebar Filters) --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">

            {{-- LEFT: TICKET LIST (MAIN AREA) --}}
            <section class="md:col-span-3 space-y-4">
                <div class="{{ $card }}">
                    {{-- Toolbar/Search (Always visible above list) --}}
                    <div class="px-4 sm:px-6 pt-4 pb-3 border-b border-gray-200">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="{{ $label }}">Search Subject/User</label>
                                <div class="relative">
                                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search subject, description or user..."
                                        class="{{ $input }} pl-10 w-full placeholder:text-gray-400">
                                    <x-heroicon-o-magnifying-glass class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                                </div>
                            </div>
                            <div>
                                <label class="{{ $label }}">Items Per Page</label>
                                <select wire:model.live="perPage" class="{{ $input }} w-full md:w-40">
                                    <option value="10">10 / page</option>
                                    <option value="20">20 / page</option>
                                    <option value="50">50 / page</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- LIST AREA (2 cards per row on large screens, 1 on small screens) --}}
                    <div class="px-4 sm:px-6 py-5">
                        @if($tickets->isEmpty())
                            <div class="py-14 text-center text-gray-500 text-sm">No tickets found matching your criteria.</div>
                        @else
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                @foreach ($tickets as $t)
                                @php
                                    $rowNo = (($tickets->firstItem() ?? 1) + $loop->index);
                                    $p = strtolower($t->priority ?? 'low');
                                    $priorityBadge = match($p) {
                                        'urgent' => 'bg-rose-600 text-white',
                                        'high' => 'bg-amber-500 text-white',
                                        'medium' => 'bg-yellow-100 text-gray-800',
                                        default => 'bg-gray-100 text-gray-800',
                                    };

                                    $statusKey = strtoupper(str_replace(' ','_', $t->status ?? 'OPEN'));
                                    $statusBadgeClass = $statusColors[$statusKey] ?? 'bg-gray-100 text-gray-600 border-gray-300';
                                @endphp
                                <div class="bg-white border border-gray-200 rounded-xl px-4 sm:px-5 py-4 hover:shadow-sm hover:border-gray-300 transition {{ $t->deleted_at ? 'opacity-50' : '' }}" wire:key="ticket-{{ $t->ticket_id }}">
                                    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                                        {{-- LEFT: Info --}}
                                        <div class="flex items-start gap-3 flex-1">
                                            <div class="{{ $ico }}">
                                                {{ strtoupper(substr($t->subject ?? 'T',0,1)) }}
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <div class="flex items-center gap-2 mb-1">
                                                    <h4 class="font-semibold text-gray-900 text-sm sm:text-base truncate">
                                                        {{ $t->subject }}
                                                    </h4>
                                                </div>
                                                
                                                <div class="flex flex-wrap items-center gap-2 mb-2">
                                                    {{-- Priority --}}
                                                    <span class="text-[11px] px-2 py-0.5 rounded-md font-medium {{ $priorityBadge }}">{{ ucfirst($p) }}</span>
                                                    
                                                    {{-- Status --}}
                                                    <span class="text-[11px] px-2 py-0.5 rounded-md font-medium border {{ $statusBadgeClass }}">
                                                        {{ ucfirst(str_replace('_',' ',$t->status)) }}
                                                    </span>

                                                    @if($t->deleted_at)
                                                        <span class="text-[11px] px-2 py-0.5 rounded-md bg-rose-100 text-rose-800">Deleted</span>
                                                    @endif
                                                </div>

                                                <p class="text-[12px] text-gray-500">
                                                    By: <span class="font-medium text-gray-700">{{ $t->user->full_name ?? '—' }}</span>
                                                </p>

                                                <p class="text-sm text-gray-600 mt-2 line-clamp-2">{{ $t->description }}</p>

                                                {{-- attachments (Simplified for card view) --}}
                                                @if($t->attachments && $t->attachments->count())
                                                    <div class="flex flex-wrap items-center gap-2 mt-2">
                                                        <span class="{{ $chip }} bg-gray-200 text-gray-600">
                                                            <x-heroicon-o-paper-clip class="w-3 h-3" />
                                                            {{ $t->attachments->count() }} Attachment(s)
                                                        </span>
                                                    </div>
                                                @endif

                                                <div class="flex flex-wrap items-center gap-2 mt-2">
                                                    <span class="{{ $chip }}"><span class="text-gray-500">Dept:</span><span class="font-medium text-gray-700">{{ $t->department->department_name ?? ($deptLookup[$t->department_id] ?? '—') }}</span></span>
                                                    <span class="{{ $chip }}"><span class="text-gray-500">Created:</span><span class="font-medium text-gray-700">{{ optional($t->created_at)->format('d M Y H:i') }}</span></span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        {{-- RIGHT: actions --}}
                                        <div class="text-right shrink-0 space-y-2">
                                            <div class="{{ $mono }}">No. {{ $rowNo }}</div>
                                            <div class="flex flex-wrap gap-2 justify-end pt-1">
                                                
                                                {{-- NEW: OPEN Button --}}
                                                <button class="{{ $btnLt }}"
                                                    wire:click="openTicketDetails({{ (int) $t->ticket_id }})"
                                                    wire:loading.attr="disabled"
                                                    wire:target="openTicketDetails({{ (int) $t->ticket_id }})">
                                                    <span wire:loading.remove wire:target="openTicketDetails({{ (int) $t->ticket_id }})">Open</span>
                                                    <span wire:loading wire:target="openTicketDetails({{ (int) $t->ticket_id }})">...</span>
                                                </button>

                                                <button class="{{ $btnBlk }}"
                                                    wire:click="openEdit({{ (int) $t->ticket_id }})"
                                                    wire:loading.attr="disabled"
                                                    wire:target="openEdit({{ (int) $t->ticket_id }})">
                                                    <span wire:loading.remove wire:target="openEdit({{ (int) $t->ticket_id }})">Edit</span>
                                                    <span wire:loading wire:target="openEdit({{ (int) $t->ticket_id }})">...</span>
                                                </button>
                                                
                                                @if(!$t->deleted_at)
                                                    <button class="{{ $btnRed }}"
                                                        wire:click="delete({{ (int) $t->ticket_id }})"
                                                        onclick="return confirm('Soft delete this ticket (move to trash)?')"
                                                        wire:loading.attr="disabled"
                                                        wire:target="delete({{ (int) $t->ticket_id }})">
                                                        <span wire:loading.remove wire:target="delete({{ (int) $t->ticket_id }})">Delete</span>
                                                        <span wire:loading wire:target="delete({{ (int) $t->ticket_id }})">...</span>
                                                    </button>
                                                @else
                                                    <button class="{{ $btnBlk }} !bg-emerald-600 hover:!bg-emerald-700"
                                                        wire:click="restore({{ (int) $t->ticket_id }})"
                                                        wire:loading.attr="disabled"
                                                        wire:target="restore({{ (int) $t->ticket_id }})">
                                                        <span wire:loading.remove wire:target="restore({{ (int) $t->ticket_id }})">Restore</span>
                                                        <span wire:loading wire:target="restore({{ (int) $t->ticket_id }})">...</span>
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    
                    @if($tickets->hasPages())
                    <div class="px-5 py-4 bg-gray-50 border-t border-gray-200">
                        <div class="flex justify-center">
                            {{ $tickets->withQueryString()->links() }}
                        </div>
                    </div>
                    @endif
                </div>
            </section>

            {{-- RIGHT: SIDEBAR FILTERS (Visible on web) --}}
            <aside class="hidden md:flex md:flex-col md:col-span-1 gap-4">
                
                {{-- DEPARTMENT FILTER --}}
                <section class="{{ $card }}">
                    <div class="px-4 py-4 border-b border-gray-200">
                        <h3 class="text-sm font-semibold text-gray-900">Filter by Department</h3>
                        <p class="text-xs text-gray-500 mt-1">Filter list based on the department that owns the ticket.</p>
                    </div>

                    <div class="px-4 py-3 max-h-96 overflow-y-auto">
                        {{-- All Departments --}}
                        <button type="button"
                                wire:click="$set('departmentFilter', '')"
                                class="w-full flex items-center justify-between gap-2 px-3 py-2 rounded-lg text-xs font-medium
                                    {{ !$departmentFilter ? 'bg-gray-900 text-white' : 'text-gray-800 hover:bg-gray-100' }}">
                            <span class="flex items-center gap-2">
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-md border border-gray-300 text-[11px]">
                                    All
                                </span>
                                <span>All Departments ({{ count($deptLookup) }})</span>
                            </span>
                            @if(!$departmentFilter)
                                <span class="text-[10px] uppercase tracking-wide opacity-80">Active</span>
                            @endif
                        </button>

                        {{-- Each Department --}}
                        <div class="mt-2 space-y-1.5">
                            @forelse($deptOptionsFormatted as $d)
                                @php $active = (string) $departmentFilter === (string) $d['id']; @endphp
                                <button type="button"
                                        wire:click="$set('departmentFilter', '{{ $d['id'] }}')"
                                        class="w-full flex items-center justify-between gap-2 px-3 py-2 rounded-lg text-xs
                                            {{ $active ? 'bg-gray-900 text-white' : 'text-gray-800 hover:bg-gray-100' }}">
                                    <span class="flex items-center gap-2">
                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-md border border-gray-300 text-[11px]">
                                            {{ substr($d['label'], 0, 2) }}
                                        </span>
                                        <span class="truncate">{{ $d['label'] }}</span>
                                    </span>
                                    @if($active)
                                        <span class="text-[10px] uppercase tracking-wide opacity-80">Active</span>
                                    @endif
                                </button>
                            @empty
                                <p class="text-xs text-gray-500">No department data found.</p>
                            @endforelse
                        </div>
                    </div>
                </section>

                {{-- PRIORITY FILTER --}}
                <section class="{{ $card }}">
                    <div class="px-4 py-4 border-b border-gray-200">
                        <h3 class="text-sm font-semibold text-gray-900">Filter by Priority</h3>
                        <p class="text-xs text-gray-500 mt-1">Filter tickets based on their urgency level.</p>
                    </div>

                    <div class="px-4 py-3 max-h-96 overflow-y-auto">
                        {{-- All Priorities --}}
                        <button type="button"
                                wire:click="$set('priorityFilter', '')"
                                class="w-full flex items-center justify-between gap-2 px-3 py-2 rounded-lg text-xs font-medium
                                    {{ !$priorityFilter ? 'bg-gray-900 text-white' : 'text-gray-800 hover:bg-gray-100' }}">
                            <span class="flex items-center gap-2">
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-md border border-gray-300 text-[11px]">
                                    All
                                </span>
                                <span>All Priorities</span>
                            </span>
                            @if(!$priorityFilter)
                                <span class="text-[10px] uppercase tracking-wide opacity-80">Active</span>
                            @endif
                        </button>

                        {{-- Each Priority --}}
                        <div class="mt-2 space-y-1.5">
                            @foreach($priorityOptions as $p)
                                @php $active = $priorityFilter === $p; @endphp
                                <button type="button"
                                        wire:click="$set('priorityFilter', '{{ $p }}')"
                                        class="w-full flex items-center justify-between gap-2 px-3 py-2 rounded-lg text-xs
                                            {{ $active ? 'bg-gray-900 text-white' : 'text-gray-800 hover:bg-gray-100' }}">
                                    <span class="flex items-center gap-2">
                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-md border border-gray-300 text-[11px]">
                                            {{ strtoupper(substr($p, 0, 1)) }}
                                        </span>
                                        <span class="truncate">{{ ucfirst($p) }}</span>
                                    </span>
                                    @if($active)
                                        <span class="text-[10px] uppercase tracking-wide opacity-80">Active</span>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    </div>
                </section>
            </aside>
        </div>


        {{-- MOBILE FILTER MODAL --}}
        @if($showFilterModal)
            <div class="fixed inset-0 z-[60] md:hidden">
                <div class="absolute inset-0 bg-black/50" wire:click="closeFilterModal"></div>
                <div class="absolute inset-x-0 bottom-0 bg-white rounded-t-2xl shadow-xl max-h-[80vh] overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900">Filter Settings</h3>
                            <p class="text-[11px] text-gray-500">Filter daftar ticket berdasarkan departemen atau prioritas.</p>
                        </div>
                        <button class="text-gray-500 hover:text-gray-700" type="button" wire:click="closeFilterModal" aria-label="Close">
                            <x-heroicon-o-x-mark class="w-5 h-5" />
                        </button>
                    </div>

                    <div class="p-4 space-y-5 max-h-[70vh] overflow-y-auto">
                        
                        {{-- Filter by Department (Mobile) --}}
                        <div>
                            <h4 class="text-sm font-semibold text-gray-800 mb-2 border-b pb-1">Filter by Department</h4>

                            <button type="button"
                                    wire:click="$set('departmentFilter', '')"
                                    class="w-full flex items-center justify-between gap-2 px-3 py-2 rounded-lg text-xs font-medium
                                        {{ !$departmentFilter ? 'bg-gray-900 text-white' : 'text-gray-800 hover:bg-gray-100' }}">
                                <span>All Departments</span>
                                @if(!$departmentFilter)
                                    <span class="text-[10px] uppercase tracking-wide opacity-80">Active</span>
                                @endif
                            </button>

                            <div class="mt-2 space-y-1.5">
                                @foreach($deptOptionsFormatted as $d)
                                    @php $active = (string) $departmentFilter === (string) $d['id']; @endphp
                                    <button type="button"
                                            wire:click="$set('departmentFilter', '{{ $d['id'] }}')"
                                            class="w-full flex items-center justify-between gap-2 px-3 py-2 rounded-lg text-xs
                                                {{ $active ? 'bg-gray-900 text-white' : 'text-gray-800 hover:bg-gray-100' }}">
                                        <span class="truncate">{{ $d['label'] }}</span>
                                        @if($active)
                                            <span class="text-[10px] uppercase tracking-wide opacity-80">Active</span>
                                        @endif
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        {{-- Filter by Priority (Mobile) --}}
                        <div>
                            <h4 class="text-sm font-semibold text-gray-800 mb-2 border-b pb-1">Filter by Priority</h4>

                            <button type="button"
                                    wire:click="$set('priorityFilter', '')"
                                    class="w-full flex items-center justify-between gap-2 px-3 py-2 rounded-lg text-xs font-medium
                                        {{ !$priorityFilter ? 'bg-gray-900 text-white' : 'text-gray-800 hover:bg-gray-100' }}">
                                <span>All Priorities</span>
                                @if(!$priorityFilter)
                                    <span class="text-[10px] uppercase tracking-wide opacity-80">Active</span>
                                @endif
                            </button>
                            
                            <div class="mt-2 space-y-1.5">
                                @foreach($priorityOptions as $p)
                                    @php $active = $priorityFilter === $p; @endphp
                                    <button type="button"
                                            wire:click="$set('priorityFilter', '{{ $p }}')"
                                            class="w-full flex items-center justify-between gap-2 px-3 py-2 rounded-lg text-xs
                                                {{ $active ? 'bg-gray-900 text-white' : 'text-gray-800 hover:bg-gray-100' }}">
                                        <span class="truncate">{{ ucfirst($p) }}</span>
                                        @if($active)
                                            <span class="text-[10px] uppercase tracking-wide opacity-80">Active</span>
                                        @endif
                                    </button>
                                @endforeach
                            </div>
                        </div>

                    </div>

                    <div class="px-4 py-3 border-t border-gray-200">
                        <button type="button"
                                class="w-full h-10 rounded-xl bg-gray-900 text-white text-sm font-medium"
                                wire:click="closeFilterModal">
                            Apply & Close
                        </button>
                    </div>
                </div>
            </div>
        @endif

        {{-- DETAIL MODAL --}}
        @if($detailModal && $selectedTicketData)
            @php 
                $ticket = (object) $selectedTicketData;
                $priority = strtolower($ticket->priority ?? '');
                $status = strtolower($ticket->status ?? 'open');
                
                // Process array data back into collections/objects for easy access in Blade
                $attachments = collect($ticket->attachments);
                $comments = collect($ticket->comments)->map(fn($c) => (object)$c);
                $assignments = collect($ticket->assignments)->map(fn($a) => (object)$a);

                $agents = $assignments->pluck('user.full_name')->filter()->unique()->values();
                $hasAgent = $agents->isNotEmpty();
            @endphp
            <div class="fixed inset-0 z-[90] flex items-center justify-center" role="dialog" aria-modal="true" wire:key="modal-detail-{{ $ticket->ticket_id }}" wire:keydown.escape.window="closeTicketDetails">
                <button type="button" class="absolute inset-0 bg-black/50" aria-label="Close overlay" wire:click="closeTicketDetails"></button>

                <div class="relative w-full max-w-7xl mx-0 md:mx-4 my-0 md:my-6 h-full md:h-[90vh] flex flex-col {{ $card }} focus:outline-none" tabindex="-1">
                    
                    {{-- Modal Header --}}
                    <div class="px-4 md:px-5 py-4 border-b border-gray-200 flex items-center justify-between sticky top-0 bg-white z-10">
                        <h3 class="text-base font-semibold text-gray-900 truncate pr-4">Ticket Details #{{ $ticket->ticket_id }}</h3>
                        <button class="text-gray-500 hover:text-gray-700" type="button" wire:click="closeTicketDetails" aria-label="Close">
                            <x-heroicon-o-x-mark class="w-5 h-5" />
                        </button>
                    </div>

                    {{-- Modal Body (Scrollable) --}}
                    <div class="overflow-y-auto p-4 sm:p-6 flex-1"> 
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                            {{-- LEFT COLUMN: Ticket Details & Comments (2/3 width) --}}
                            <div class="lg:col-span-2 space-y-6">

                                {{-- Ticket Details Card --}}
                                <div class="bg-white rounded-xl shadow-sm border-2 border-black p-4 md:p-5">
                                    <div class="flex flex-col md:flex-row md:items-start justify-between gap-4 mb-4">
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2 mb-2">
                                                <span class="text-xs font-mono font-medium text-gray-500 bg-gray-100 px-2 py-0.5 rounded border border-gray-200">
                                                    #{{ $ticket->ticket_id }}
                                                </span>

                                                @php
                                                $isHigh = $priority === 'high' || $priority === 'urgent';
                                                $isMedium = $priority === 'medium';
                                                $isLow = $priority === 'low' || $priority === '';
                                                @endphp
                                                <span @class([ 'inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide border' , 'bg-orange-50 text-orange-800 border-orange-200'=> $isHigh,
                                                    'bg-yellow-50 text-yellow-800 border-yellow-200' => $isMedium,
                                                    'bg-gray-50 text-gray-700 border-gray-200' => $isLow,
                                                    ])>
                                                    <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21H21.75L15.92 15.17M4.5 13.5L10.33 19.33M15.92 8.83L10.08 3M4.5 9L10.33 14.83M15.92 14.83L10.08 20.66M4.5 19.5L10.33 13.67M15.92 3.5L10.08 9.33M4.5 4.5L10.33 10.33" />
                                                    </svg>
                                                    {{ $priority ? ucfirst($priority) : 'Low' }}
                                                </span>
                                            </div>

                                            <h2 class="text-xl md:text-2xl font-bold text-gray-900 break-words leading-tight">
                                                {{ $ticket->subject }}
                                            </h2>
                                        </div>

                                        {{-- Status Badge --}}
                                        @php
                                        $isOpen = $status === 'open';
                                        $isAssignedOrProgress = in_array($status, ['assigned','in_progress','process'], true);
                                        $isResolved = in_array($status, ['resolved','closed','complete'], true);
                                        @endphp
                                        <div class="flex flex-col items-end gap-2">
                                            <span @class([ 'inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide border shadow-sm' , 'bg-yellow-100 text-yellow-800 border-yellow-200'=> $isOpen,
                                                'bg-blue-100 text-blue-800 border-blue-200' => $isAssignedOrProgress,
                                                'bg-green-100 text-green-800 border-green-200' => $isResolved,
                                                'bg-gray-100 text-gray-800 border-gray-200' => (! $isOpen && ! $isAssignedOrProgress && ! $isResolved),
                                                ])>
                                                @if($isResolved) <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15L15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                @elseif($isAssignedOrProgress) <svg class="w-4 h-4 animate-spin-slow" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.18-3.18M18.001 19.644v-4.992m0 0h4.992m-4.993 0l-3.18-3.18M4.002 9.348h4.992v-.001M2.985 4.644v4.992m0 0h4.992m-4.993 0l3.18-3.18M18.001 4.644v4.992m0 0h4.992m-4.993 0l-3.18-3.18" /></svg>
                                                @else <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                @endif
                                                {{ str_replace('_', ' ', ucfirst($status)) }}
                                            </span>
                                        </div>
                                    </div>

                                    {{-- Description --}}
                                    <div class="prose prose-sm max-w-none text-gray-700 bg-gray-50 rounded-lg p-4 border border-gray-100 mt-4">
                                        <p class="whitespace-pre-wrap leading-relaxed">{{ $ticket->description }}</p>
                                    </div>

                                    {{-- Attachments --}}
                                    @if($attachments->isNotEmpty())
                                    <div class="mt-6 pt-4 border-t border-dashed border-gray-200">
                                        <div class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-3 flex items-center gap-2">
                                            <x-heroicon-o-paper-clip class="w-4 h-4" />
                                            Attachments ({{ $attachments->count() }})
                                        </div>

                                        <div class="space-y-3 text-sm">
                                            @php
                                            $okImg = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp', 'svg'];
                                            $atts = $attachments
                                                ->map(function ($a) use ($okImg) {
                                                    $url = (string) ($a['file_url'] ?? '');
                                                    $name = (string) ($a['original_filename'] ?? '');
                                                    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                                                    return (object) ['url' => $url, 'name' => $name ?: basename($url) ?: 'attachment', 'ext' => $ext,];
                                                })
                                                ->filter(fn($x) => $x->url);
                                            $images = $atts->filter(fn($x) => in_array($x->ext, $okImg, true))->values();
                                            $others = $atts->reject(fn($x) => in_array($x->ext, $okImg, true))->values();
                                            @endphp

                                            {{-- A. IMAGES GRID --}}
                                            @if ($images->isNotEmpty())
                                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 mb-4">
                                                @foreach ($images as $img)
                                                <a href="{{ $img->url }}" target="_blank" class="block group relative aspect-square overflow-hidden rounded-lg border border-gray-200 bg-gray-50 hover:border-gray-400 transition">
                                                    <img src="{{ $img->url }}" alt="{{ $img->name }}" class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
                                                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-colors"></div>
                                                </a>
                                                @endforeach
                                            </div>
                                            @endif

                                            {{-- B. OTHER FILES LIST (PDFs, Docs, Zips) --}}
                                            @if ($others->isNotEmpty())
                                            <div class="grid grid-cols-1 gap-2">
                                                @foreach ($others as $f)
                                                <div class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 bg-white hover:bg-gray-50 transition">
                                                    {{-- File Icon Box --}}
                                                    <div class="w-10 h-10 rounded-md bg-gray-900 text-white flex items-center justify-center shrink-0 text-[10px] font-bold uppercase">
                                                        {{ $f->ext ?: 'FILE' }}
                                                    </div>

                                                    {{-- Filename --}}
                                                    <div class="min-w-0 flex-1">
                                                        <div class="truncate text-sm font-medium text-gray-900" title="{{ $f->name }}">
                                                            {{ $f->name }}
                                                        </div>
                                                    </div>

                                                    {{-- Actions --}}
                                                    <div class="shrink-0 flex items-center gap-2">
                                                        <a href="{{ $f->url }}" target="_blank"
                                                            class="px-3 py-1.5 text-xs font-medium rounded-md border border-gray-300 hover:bg-gray-100 text-gray-700 transition">
                                                            Open
                                                        </a>
                                                    </div>
                                                </div>
                                                @endforeach
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                    @endif
                                    {{-- Meta Footer --}}
                                    <div class="mt-6 pt-4 border-t border-gray-100 flex flex-wrap items-center justify-between gap-4 text-xs text-gray-500">
                                        <div class="flex items-center gap-4">
                                            <div class="flex items-center gap-1.5">
                                                <x-heroicon-o-clock class="w-4 h-4 text-gray-400" />
                                                <span>Created {{ optional(new Carbon($ticket->created_at))->format('d M Y, H:i') }}</span>
                                            </div>
                                            <div class="flex items-center gap-1.5">
                                                <x-heroicon-o-arrow-path class="w-4 h-4 text-gray-400" />
                                                <span>Updated {{ optional(new Carbon($ticket->updated_at))->diffForHumans() }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Discussion Card (Display only) --}}
                                <div class="bg-white rounded-xl shadow-sm border-2 border-black p-4 md:p-5">
                                    <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center gap-2">
                                        <x-heroicon-o-chat-bubble-bottom-center-text class="w-5 h-5" />
                                        Discussion (Display Only)
                                    </h3>
                                    
                                    {{-- Comments Timeline --}}
                                    <div class="space-y-6 relative before:absolute before:inset-0 before:ml-4 before:-translate-x-px md:before:ml-[19px] before:h-full before:w-0.5 before:bg-gray-100">
                                        @forelse ($comments as $comment)
                                        @php
                                        $name = $comment->user['full_name'] ?? $comment->user['name'] ?? 'User';
                                        $init = $initials($name);
                                        @endphp

                                        <div class="relative flex gap-3 group">
                                            <div class="flex-shrink-0 relative z-10">
                                                <span class="inline-flex h-8 w-8 rounded-full bg-white border-2 border-gray-200 text-gray-600 items-center justify-center text-[10px] font-bold ring-4 ring-white shadow-sm">
                                                    {{ $init }}
                                                </span>
                                            </div>

                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center justify-between gap-2 mb-1">
                                                    <span class="text-sm font-bold text-gray-900">{{ $name }}</span>
                                                    <span class="text-[10px] text-gray-400" title="{{ (new Carbon($comment->created_at))->format('d M Y H:i') }}">
                                                        {{ (new Carbon($comment->created_at))->diffForHumans() }}
                                                    </span>
                                                </div>

                                                <div class="rounded-xl px-4 py-3 text-sm shadow-sm border leading-relaxed bg-gray-50 border-gray-200 text-gray-800 rounded-tl-none">
                                                    <p class="whitespace-pre-wrap">{{ $comment->comment_text }}</p>
                                                </div>
                                            </div>
                                        </div>
                                        @empty
                                        <div class="relative z-10 bg-white p-6 text-center rounded-xl border-2 border-dashed border-gray-200 mx-4">
                                            <p class="text-sm text-gray-500">No comments yet.</p>
                                        </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>

                            {{-- RIGHT COLUMN: Sidebar Info (1/3 width) --}}
                            <div class="space-y-6">
                                {{-- Info Card --}}
                                <div class="bg-white rounded-xl shadow-sm border-2 border-black p-4 md:p-5">
                                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4 pb-2 border-b border-gray-100">
                                        Ticket Info
                                    </h3>

                                    <div class="space-y-3 text-sm">

                                        {{-- Requester Card --}}
                                        <div class="group flex items-center gap-3 p-3 rounded-lg border border-gray-200 bg-white shadow-sm hover:border-indigo-200 transition-colors">
                                            <span class="flex-shrink-0 p-2 rounded-full bg-indigo-50 text-indigo-600">
                                                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.25a6.75 6.75 0 0113.499 0h-13.5z" /></svg>
                                            </span>
                                            <div class="flex flex-col overflow-hidden">
                                                <span class="text-xs text-gray-400 font-medium mb-0.5">Requested By</span>
                                                <span class="font-semibold text-gray-900 truncate">{{ $ticket->user['full_name'] ?? 'Unknown User' }}</span>
                                                <span class="text-[10px] text-gray-500 truncate flex items-center gap-1 mt-0.5">
                                                    {{ $ticket->requester_department['department_name'] ?? 'No Dept' }}
                                                </span>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 gap-3">
                                            {{-- Department --}}
                                            <div class="flex items-center justify-between p-2.5 rounded-md bg-gray-50 border border-gray-100">
                                                <span class="text-xs font-medium text-gray-500">Department</span>
                                                <span class="text-xs font-semibold text-gray-700">
                                                    {{ $ticket->department['department_name'] ?? '-' }}
                                                </span>
                                            </div>

                                            {{-- Agent --}}
                                            <div class="flex items-center justify-between p-2.5 rounded-md bg-gray-50 border border-gray-100">
                                                <span class="text-xs font-medium text-gray-500">Assigned Agent</span>

                                                @if($hasAgent)
                                                <div class="flex items-center gap-1.5">
                                                    <x-heroicon-o-check-circle class="w-4 h-4 text-emerald-500" />
                                                    <span class="text-xs font-semibold text-gray-900">{{ $agents->join(', ') }}</span>
                                                </div>
                                                @else
                                                <span class="text-[10px] uppercase font-bold text-gray-400 tracking-wide">
                                                    Pending
                                                </span>
                                                @endif
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="px-4 md:px-5 py-3 border-t border-gray-200 flex justify-end sticky bottom-0 bg-white z-10">
                        <button type="button" class="{{ $btnLt }}" wire:click="closeTicketDetails">Close Details</button>
                    </div>
                </div>
            </div>
        @endif

        {{-- MODAL: EDIT (z-index is high to ensure it sits on top of the mobile filter) --}}
        @if($modal)
        <div class="fixed inset-0 z-[100] flex items-center justify-center" role="dialog" aria-modal="true" wire:key="modal-ticket" wire:keydown.escape.window="closeModal">
            <button type="button" class="absolute inset-0 bg-black/50" aria-label="Close overlay" wire:click="closeModal"></button>

            <div class="relative w-full max-w-2xl mx-4 {{ $card }} focus:outline-none" tabindex="-1">
                <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-base font-semibold text-gray-900">Edit Ticket #{{ $editingTicketId }}</h3>
                    <button class="text-gray-500 hover:text-gray-700" type="button" wire:click="closeModal" aria-label="Close">
                        <x-heroicon-o-x-mark class="w-5 h-5" />
                    </button>
                </div>

                <form class="p-5" wire:submit.prevent="update">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="{{ $label }}">Department</label>
                            <select class="{{ $input }}" wire:model.defer="department_id">
                                <option value="">Choose department</option>
                                @foreach ($deptLookup as $did => $dname)
                                <option value="{{ $did }}">{{ $dname }}</option>
                                @endforeach
                            </select>
                            @error('department_id') <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="{{ $label }}">Priority</label>
                            <select class="{{ $input }}" wire:model.defer="priority">
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                            @error('priority') <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p> @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="{{ $label }}">Subject</label>
                            <input type="text" class="{{ $input }}" wire:model.defer="subject">
                            @error('subject') <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p> @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="{{ $label }}">Description</label>
                            <textarea class="{{ $input }} h-28" wire:model.defer="description"></textarea>
                            @error('description') <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="{{ $label }}">Status</label>
                            <select class="{{ $input }}" wire:model.defer="status">
                                <option value="OPEN">Open</option>
                                <option value="IN_PROGRESS">In Progress</option>
                                <option value="RESOLVED">Resolved</option>
                                <option value="CLOSED">Closed</option>
                            </select>
                            @error('status') <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-3 border-t border-gray-200 pt-4">
                        <button type="button" class="{{ $btnLt }}" wire:click="closeModal">Cancel</button>
                        <button type="submit" class="{{ $btnBlk }}" wire:loading.attr="disabled" wire:target="update">
                            <span wire:loading.remove wire:target="update">Update</span>
                            <span class="inline-flex items-center gap-2" wire:loading wire:target="update">
                                <x-heroicon-o-arrow-path class="animate-spin h-4 w-4" />
                                Processing…
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endif
    </main>
</div>