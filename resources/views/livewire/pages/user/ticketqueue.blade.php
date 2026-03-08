<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    {{-- Header --}}
    <div class="bg-white rounded-xl shadow-sm border-2 border-black p-4 md:p-6 mb-4 md:mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <h1 class="text-xl md:text-2xl font-bold text-gray-900">Support Ticket Queue</h1>

            {{-- Navigation Tabs --}}
            <div class="inline-flex rounded-lg overflow-hidden bg-gray-100 border border-gray-200 self-start md:self-center">
                <button
                    type="button"
                    wire:click="$set('tab','queue')"
                    @class([ 'px-3 md:px-4 py-2 text-sm font-medium transition-colors border-r border-gray-200' , 'bg-gray-900 text-white'=> $tab === 'queue',
                    'text-gray-700 hover:text-gray-900 hover:bg-gray-50' => $tab !== 'queue',
                    ])>
                    Ticket Queue
                </button>
                
                {{-- MODIFIED: Wrapper for relative badge positioning --}}
                <div class="relative">
                    <button
                        type="button"
                        wire:click="$set('tab','claims')"
                        @class([ 
                            'px-3 md:px-4 py-2 text-sm font-medium transition-colors', 
                            'bg-gray-900 text-white'=> $tab === 'claims',
                            'text-gray-700 hover:text-gray-900 hover:bg-gray-50' => $tab !== 'claims',
                        ])>
                        My Claims
                    </button>
                    
                    {{-- Badge Logic - Positioned Absolutely (Removed animate-pulse) --}}
                    @if ($totalUnreadClaims > 0)
                    <span 
                        class="absolute -top-1 right-0 text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-red-600 text-white leading-none shadow-lg"
                        title="{{ $totalUnreadClaims }} unread comments"
                    >
                        {{ $totalUnreadClaims }}
                    </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="bg-white rounded-xl shadow-sm border-2 border-black p-4 md:p-5 min-h-[600px]">
        {{-- QUEUE TAB --}}
        @if ($tab === 'queue')
        <div wire:key="queue-tab-container">
            <div class="flex flex-col md:flex-row md:items-center gap-4 pb-4 mb-4 border-b border-gray-100">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-3 w-full">
                    {{-- Search --}}
                    <div class="md:col-span-2">
                        <div class="relative">
                            <input
                                type="text"
                                wire:model.debounce.400ms="search"
                                placeholder="Search subject or description..."
                                class="w-full pl-9 px-3 py-2 text-sm text-gray-900 placeholder:text-gray-400 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent">
                            {{-- ORIGINAL SVG: Magnifying Glass --}}
                            <x-heroicon-o-magnifying-glass class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
                        </div>
                    </div>

                    {{-- Status Filter --}}
                    <select wire:model.live="status" class="w-full px-3 py-2 text-sm text-gray-900 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent">
                        <option value="">All Status</option>
                        <option value="OPEN">Open</option>
                        <option value="IN_PROGRESS">In Progress</option>
                        <option value="RESOLVED">Resolved</option>
                        <option value="CLOSED">Closed</option>
                    </select>

                    {{-- Priority Filter --}}
                    <select wire:model.live="priority" class="w-full px-3 py-2 text-sm text-gray-900 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent">
                        <option value="">All Priority</option>
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>
            </div>

            @if(!$tickets || $tickets->isEmpty())
            <div class="rounded-xl border-2 border-dashed border-gray-300 p-12 text-center">
                {{-- ORIGINAL SVG: Document/File icon --}}
                <x-heroicon-o-document-text class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-medium text-gray-900">No tickets found</h3>
                <p class="mt-1 text-sm text-gray-500">Tidak ada tiket yang cocok dengan filter Anda.</p>
            </div>
            @else
            <div class="space-y-4">
                @foreach ($tickets as $t)
                @php
                $priority = strtolower($t->priority ?? '');
                $statusUp = strtoupper($t->status ?? 'OPEN');
                $statusLabel = ucfirst(strtolower(str_replace('_',' ',$statusUp)));
                $unreadCount = $t->unread_comments_count ?? 0; // Retrieve the count
                
                $isHigh = $priority === 'high';
                $isMedium = $priority === 'medium';

                // Badge Styles
                $priorityBadge = $isHigh
                ? 'bg-orange-50 text-orange-800 border-orange-200'
                : ($isMedium
                ? 'bg-yellow-50 text-yellow-800 border-yellow-200'
                : 'bg-gray-50 text-gray-700 border-gray-200');

                $statusBadge = match(true){
                $statusUp === 'OPEN' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                in_array($statusUp, ['ASSIGNED','IN_PROGRESS']) => 'bg-blue-100 text-blue-800 border-blue-200',
                in_array($statusUp, ['RESOLVED','CLOSED']) => 'bg-green-100 text-green-800 border-green-200',
                default => 'bg-gray-100 text-gray-700 border-gray-200'
                };
                @endphp

                {{-- Card is now clickable using window.location.href and ULID --}}
                <div 
                    onclick="window.location.href='{{ route('user.ticket.show', $t->ulid) }}'"
                    class="group relative bg-white rounded-xl border-2 border-black p-5 hover:shadow-md transition-all duration-200 cursor-pointer"
                >
                    {{-- Header --}}
                    <div class="flex flex-col md:flex-row md:items-start justify-between gap-4 mb-3">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1.5">
                                <span class="text-xs font-mono font-medium text-gray-500 bg-gray-100 px-2 py-0.5 rounded">#{{ $t->ticket_id }}</span>
                                <span class="text-[10px] px-2 py-0.5 rounded-md uppercase font-bold tracking-wide border {{ $priorityBadge }}">
                                    {{ $priority ? ucfirst($priority) : 'Low' }}
                                </span>
                                
                                {{-- UNREAD COMMENT COUNT BADGE (Queue Tab) - REMOVED AS REQUESTED --}}
                            </div>
                            <h3 class="text-lg font-bold text-gray-900 truncate">
                                {{ $t->subject }}
                            </h3>

                            @if($t->requester)
                            <div class="mt-1 flex items-center gap-1 text-xs text-gray-600">
                                <span class="text-gray-400">From:</span>
                                <span class="font-medium text-gray-900 bg-gray-50 px-1.5 py-0.5 rounded border border-gray-200">
                                    {{ $t->requester->full_name ?? $t->requester->email }}
                                </span>
                            </div>
                            @endif
                        </div>

                        <span class="shrink-0 inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide border {{ $statusBadge }}">
                            {{ $statusLabel }}
                        </span>
                    </div>

                    {{-- Description --}}
                    <p class="text-sm text-gray-600 leading-relaxed line-clamp-3 mb-4">
                        {{ $t->description }}
                    </p>

                    {{-- Footer Info --}}
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pt-4 border-t border-gray-100">
                        <div class="text-[11px] text-gray-500 flex flex-col gap-1">
                            <div class="flex items-center gap-1">
                                {{-- ORIGINAL SVG: Clock (Time) --}}
                                <x-heroicon-o-clock class="w-3 h-3" />
                                <span>Created {{ \Carbon\Carbon::parse($t->created_at)->diffForHumans() }}</span>
                            </div>
                            @if($t->updated_at != $t->created_at)
                            <div class="flex items-center gap-1">
                                {{-- ORIGINAL SVG: Arrow Path (Update) --}}
                                <x-heroicon-o-arrow-path class="w-3 h-3" />
                                <span>Updated {{ optional($t->updated_at)->diffForHumans() }}</span>
                            </div>
                            @endif
                        </div>

                        <div class="w-full sm:w-auto flex flex-col sm:flex-row items-end sm:items-center gap-3">
                            {{-- Claim Button --}}
                            <button
                                onclick="event.stopPropagation()"
                                wire:click="claim({{ $t->ticket_id }})"
                                wire:loading.attr="disabled"
                                wire:target="claim"
                                type="button"
                                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-gray-900 rounded-lg hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200 shadow-sm">
                                {{-- ORIGINAL SVG: Arrow Up Tray (Claim/Assign) --}}
                                <x-heroicon-o-arrow-up-tray class="w-4 h-4" />
                                <span wire:loading.remove wire:target="claim">Claim Ticket</span>
                                <span wire:loading wire:target="claim">Processing...</span>
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $tickets->onEachSide(1)->links() }}
            </div>
            @endif
        </div>
        @endif

        {{-- CLAIMS TAB (KANBAN) --}}
        @if ($tab === 'claims')
        <div wire:key="claims-tab-container"
            x-data="{
                    draggingId: null,
                    draggingFrom: null,
                    dragStart(id, fromStatus) {
                        this.draggingId = id;
                        this.draggingFrom = fromStatus;
                    },
                    drop(toStatus) {
                        if (!this.draggingId || toStatus === this.draggingFrom) {
                            this.draggingId = null;
                            this.draggingFrom = null;
                            return;
                        }
                        $wire.moveClaim(this.draggingId, toStatus);
                        this.draggingId = null;
                        this.draggingFrom = null;
                    }
                }"
            class="h-full">
            {{-- Filter bar --}}
            <div class="flex flex-col md:flex-row md:items-center gap-4 pb-4 mb-4 border-b border-gray-100">
                <div class="w-full md:w-1/4">
                    <select wire:model.live="claimPriority" class="w-full px-3 py-2 text-sm text-gray-900 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent">
                        <option value="">All Priority</option>
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>
            </div>

            @if(!$claims || $claims->isEmpty())
            <div class="rounded-xl border-2 border-dashed border-gray-300 p-12 text-center">
                {{-- ORIGINAL SVG: Document/File icon --}}
                <x-heroicon-o-document-text class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-medium text-gray-900">No claims yet</h3>
                <p class="mt-1 text-sm text-gray-500">Anda belum meng-claim tiket apapun.</p>
            </div>
            @else
            @php
            // Group claims by status for Kanban columns
            $groupedClaims = $claims->groupBy(function ($assignment) {
            $status = strtoupper(optional($assignment->ticket)->status ?? 'OPEN');

            return match (true) {
            in_array($status, ['ASSIGNED', 'IN_PROGRESS']) => 'IN_PROGRESS',
            in_array($status, ['RESOLVED']) => 'RESOLVED',
            default => $status,
            };
            });
            @endphp

            {{-- Layout: 2 Columns --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 h-full items-start">
                @foreach ($kanbanColumns as $statusKey => $label)
                <div
                    class="flex flex-col rounded-xl border-2 border-gray-200 bg-gray-50 p-3 h-full min-h-[500px]"
                    x-on:dragover.prevent
                    x-on:drop.prevent="drop('{{ $statusKey }}')">
                    {{-- Column Header --}}
                    <div class="flex items-center justify-between mb-3 px-1">
                        <h3 class="text-xs font-bold tracking-wider uppercase text-gray-600">
                            {{ $label }}
                        </h3>
                        <span class="bg-gray-200 text-gray-700 text-[10px] font-bold px-2 py-0.5 rounded-full">
                            {{ ($groupedClaims[$statusKey] ?? collect())->count() }}
                        </span>
                    </div>

                    {{-- Column Body --}}
                    <div class="space-y-3 flex-1">
                        @forelse ($groupedClaims[$statusKey] ?? [] as $asgn)
                        @php
                        $t = $asgn->ticket;
                        // The badge should be shown here as the ticket is claimed
                        $unreadCount = $asgn->unread_count ?? 0; // Retrieve the count from the join
                        if (!$t) continue;

                        $prio = strtolower($t->priority ?? '');
                        $cardBorderClass = match($prio) {
                        'high' => 'border-l-4 border-l-orange-500 border-y border-r border-gray-200',
                        'medium' => 'border-l-4 border-l-yellow-400 border-y border-r border-gray-200',
                        default => 'border border-gray-200',
                        };
                        @endphp

                        <div
                            class="relative bg-white rounded-lg p-3 shadow-sm hover:shadow-md transition-all cursor-grab active:cursor-grabbing {{ $cardBorderClass }}"
                            draggable="true"
                            x-on:dragstart="dragStart({{ $t->ticket_id }}, '{{ strtoupper($t->status ?? 'OPEN') }}')"
                            x-on:dragend="draggingId = null; draggingFrom = null">
                            <div class="flex items-start justify-between gap-2 mb-2">
                                <span class="text-[10px] font-mono text-gray-500">#{{ $t->ticket_id }}</span>
                                <div class="flex items-center gap-2">
                                    @if($prio === 'high')
                                    <span class="text-[9px] font-bold text-orange-700 bg-orange-50 px-1.5 py-0.5 rounded border border-orange-100">HIGH</span>
                                    @elseif($prio === 'medium')
                                    <span class="text-[9px] font-bold text-yellow-700 bg-yellow-50 px-1.5 py-0.5 rounded border border-yellow-100">MED</span>
                                    @endif

                                    {{-- UNREAD COMMENT COUNT BADGE (Claims Tab) --}}
                                    @if ($unreadCount > 0)
                                    <span class="text-[9px] bg-red-500 text-white px-1.5 py-0.5 rounded-full font-bold leading-none" title="{{ $unreadCount }} unread comments">
                                        {{ $unreadCount }}
                                    </span>
                                    @endif
                                </div>
                            </div>

                            <a href="{{ route('user.ticket.show', $t) }}" class="block text-sm font-semibold text-gray-900 line-clamp-2 hover:text-blue-600 mb-1.5">
                                {{ $t->subject }}
                            </a>

                            <div class="flex items-center justify-between text-[10px] text-gray-500 border-t border-gray-50 pt-2 mt-2">
                                <div class="flex items-center gap-1" title="Claimed at">
                                    {{-- ORIGINAL SVG: Clock (Time) --}}
                                    <x-heroicon-o-clock class="w-3 h-3" />
                                    {{ \Carbon\Carbon::parse($asgn->created_at)->diffForHumans(null, true) }}
                                </div>
                                @if($t->requester)
                                <div class="flex items-center gap-1 truncate max-w-[80px]" title="{{ $t->requester->full_name }}">
                                    {{-- ORIGINAL SVG: User Circle (Requester) --}}
                                    <x-heroicon-o-user-circle class="w-3 h-3" />
                                    <span class="truncate">{{ explode(' ', $t->requester->full_name)[0] }}</span>
                                </div>
                                @endif
                            </div>
                        </div>
                        @empty
                        <div class="flex flex-col items-center justify-center h-24 border-2 border-dashed border-gray-200 rounded-lg text-gray-400">
                            <span class="text-xs italic">Empty</span>
                        </div>
                        @endforelse
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
        @endif
    </div>
</div>