<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    {{-- Header --}}
    <div class="bg-white rounded-xl shadow-sm border-2 border-black p-4 md:p-6 mb-4 md:mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <h1 class="text-xl md:text-2xl font-bold text-gray-900">Support Ticket System</h1>

            @php
                $isCreate = request()->routeIs('create-ticket') || request()->is('create-ticket');
                $isStatus = request()->routeIs('ticketstatus') || request()->is('ticketstatus');
            @endphp

            {{-- Navigation Tabs --}}
            <div class="inline-flex rounded-lg overflow-hidden bg-gray-100 border border-gray-200 self-start md:self-center">
                <a href="{{ route('create-ticket') }}"
                   @class([
                       'px-3 md:px-4 py-2 text-sm font-medium transition-colors inline-flex items-center border-r border-gray-200',
                       'bg-gray-900 text-white' => $isCreate,
                       'text-gray-700 hover:text-gray-900 hover:bg-gray-50' => !$isCreate,
                   ])>
                    Create Ticket
                </a>
                <a href="{{ route('ticketstatus') }}"
                   @class([
                       'px-3 md:px-4 py-2 text-sm font-medium transition-colors inline-flex items-center',
                       'bg-gray-900 text-white' => $isStatus,
                       'text-gray-700 hover:text-gray-900 hover:bg-gray-50' => !$isStatus,
                   ])>
                    Ticket Status
                </a>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="bg-white rounded-xl shadow-sm border-2 border-black p-4 md:p-5">
        {{-- Filters --}}
        <div class="flex flex-col md:flex-row md:items-center gap-4 pb-4 mb-4 border-b border-gray-100">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 w-full">
                <select wire:model.live="statusFilter" class="w-full px-3 py-2 text-sm text-gray-900 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent">
                    <option value="">All Status</option>
                    <option value="open">Open</option>
                    <option value="in_progress">In Progress</option>
                    <option value="resolved">Resolved</option>
                    <option value="closed">Closed</option>
                </select>

                <select wire:model.live="priorityFilter" class="w-full px-3 py-2 text-sm text-gray-900 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent">
                    <option value="">All Priority</option>
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                </select>

                <select wire:model.live="departmentFilter" class="w-full px-3 py-2 text-sm text-gray-900 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent">
                    <option value="">All Departments</option>
                    @foreach ($departments as $dept)
                        <option value="{{ $dept->department_id }}">{{ $dept->department_name }}</option>
                    @endforeach
                </select>

                <select wire:model.live="sortFilter" class="w-full px-3 py-2 text-sm text-gray-900 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent">
                    <option value="recent">Recent first</option>
                    <option value="oldest">Oldest first</option>
                    <option value="due">Nearest due</option>
                </select>
            </div>
        </div>

        {{-- Cards --}}
        <div class="space-y-4">
            @forelse ($tickets as $t)
                @php
                    $priority = strtolower($t->priority ?? '');
                    $statusUp = strtoupper($t->status ?? 'OPEN');
                    $statusLabel = ucfirst(strtolower(str_replace('_',' ',$statusUp)));
                    $userName = $t->user->full_name ?? $t->user->name ?? 'User';
                    $initial = strtoupper(mb_substr($userName, 0, 1));
                    $isOpen = $statusUp === 'OPEN';
                    $isAssignedOrProgress = in_array($statusUp, ['ASSIGNED','IN_PROGRESS'], true);
                    $isResolvedOrClosed = in_array($statusUp, ['RESOLVED','CLOSED'], true);
                    $hasAgent = (int)($t->agent_count ?? 0) > 0;
                    $agents = collect($t->assignments ?? [])->pluck('user.full_name')->filter()->unique()->values();
                    // Retrieve the unread count
                    $unreadCount = $t->unread_comments_count ?? 0; 
                @endphp

                <div class="group relative bg-white rounded-xl border-2 border-black p-5 hover:shadow-md transition-shadow duration-200">
                    {{-- Full Clickable Area --}}
                    <a href="{{ route('user.ticket.show', $t) }}"
                       class="absolute inset-0 z-20 focus:outline-none focus:ring-2 focus:ring-gray-900 rounded-xl"
                       aria-label="Open ticket #{{ $t->ticket_id }}"></a>

                    <div class="flex flex-col md:flex-row md:items-start justify-between gap-4 mb-3 relative z-0 pointer-events-none">
                        <div class="flex-1 min-w-0">
                            {{-- Subject and Unread Count --}}
                            <div class="flex items-center gap-2 mb-2">
                                <h3 class="text-lg font-bold text-gray-900 truncate">{{ $t->subject }}</h3>
                                @if ($unreadCount > 0)
                                <span class="text-[10px] bg-red-500 text-white px-2 py-0.5 rounded-full font-bold leading-none animate-pulse shrink-0" title="{{ $unreadCount }} unread agent replies">
                                    {{ $unreadCount }}
                                </span>
                                @endif
                            </div>

                            <div class="flex flex-wrap items-center gap-2 text-xs">
                                <span class="font-mono font-medium text-gray-800 inline-flex items-center gap-1 bg-gray-100 px-2 py-1 rounded">
                                    <x-heroicon-o-hashtag class="w-3 h-3" /> {{ $t->ticket_id }}
                                </span>

                                @php
                                    $isHigh   = $priority === 'high';
                                    $isMedium = $priority === 'medium';
                                    $isLow    = $priority === 'low' || $priority === '';
                                @endphp
                                <span @class([
                                    'inline-flex items-center gap-1 px-2 py-1 rounded-md font-medium border',
                                    'bg-orange-50 text-orange-800 border-orange-200' => $isHigh,
                                    'bg-yellow-50 text-yellow-800 border-yellow-200' => $isMedium,
                                    'bg-gray-50 text-gray-700 border-gray-200' => $isLow,
                                ])>
                                    <x-heroicon-o-bolt class="w-3 h-3" />
                                    {{ $priority ? ucfirst($priority) : 'Low' }}
                                </span>

                                @if ($t->department)
                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md border border-gray-200 bg-gray-50 text-gray-700 font-medium">
                                        <x-heroicon-o-building-office-2 class="w-3 h-3" />
                                        <span>{{ $t->department->department_name }}</span>
                                    </span>
                                @endif

                                <span class="hidden md:inline text-gray-300">•</span>

                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md border border-gray-200 bg-gray-50 text-gray-700">
                                    <span class="inline-flex items-center justify-center w-4 h-4 rounded-full bg-gray-200 text-[10px] font-bold text-gray-600">
                                        {{ $initial }}
                                    </span>
                                    <span class="font-medium">{{ $userName }}</span>
                                </span>

                                @if($hasAgent)
                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md border border-emerald-200 bg-emerald-50 text-emerald-800 font-medium">
                                        <x-heroicon-o-user-circle class="w-3 h-3" />
                                        <span>Agent Assigned</span>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <span @class([
                            'shrink-0 inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide border',
                            'bg-yellow-100 text-yellow-800 border-yellow-200' => $isOpen,
                            'bg-blue-100 text-blue-800 border-blue-200'   => $isAssignedOrProgress,
                            'bg-green-100 text-green-800 border-green-200'=> $isResolvedOrClosed,
                            'bg-gray-100 text-gray-800 border-gray-200'   => (!$isOpen && !$isAssignedOrProgress && !$isResolvedOrClosed),
                        ])>
                            {{ $statusLabel }}
                        </span>
                    </div>

                    <p class="text-sm text-gray-600 leading-relaxed relative z-0 pointer-events-none line-clamp-2 mb-4">
                        {{ $t->description }}
                    </p>

                    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 pt-4 border-t border-gray-100">
                        <div class="text-[11px] text-gray-500 relative z-0 pointer-events-none flex flex-col gap-1">
                            <div class="flex items-center gap-1">
                                <x-heroicon-o-clock class="w-3 h-3" />
                                <span>Created {{ optional($t->created_at)->format('d M Y, H:i') }}</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <x-heroicon-o-arrow-path class="w-3 h-3" />
                                <span>Updated {{ optional($t->updated_at)->diffForHumans() }}</span>
                            </div>
                        </div>

                        @if (in_array($statusUp, ['OPEN','ASSIGNED','IN_PROGRESS','RESOLVED','CLOSED'], true))
                            <div class="relative z-30 pointer-events-auto">
                                @if (in_array($statusUp, ['OPEN','ASSIGNED','IN_PROGRESS'], true))
                                    <button
                                        @disabled(!$hasAgent)
                                        @class([
                                            'inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg transition-colors',
                                            $hasAgent
                                                ? 'bg-gray-900 text-white hover:bg-gray-800'
                                                : 'bg-gray-100 text-gray-400 cursor-not-allowed'
                                        ])
                                        @if($hasAgent)
                                            wire:click.stop="markComplete({{ (int) $t->ticket_id }})"
                                        @endif
                                        wire:loading.attr="disabled"
                                        wire:target="markComplete"
                                        type="button"
                                        title="{{ $hasAgent ? 'Mark this ticket as Resolved' : 'Cannot resolve: no agent assigned' }}">
                                        <x-heroicon-o-check-circle class="w-4 h-4" />
                                        Mark as Resolved
                                    </button>
                                @elseif ($statusUp === 'RESOLVED')
                                    <button
                                        wire:click.stop="markComplete({{ (int) $t->ticket_id }})"
                                        wire:loading.attr="disabled"
                                        wire:target="markComplete"
                                        type="button"
                                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-black rounded-lg hover:bg-gray-800 transition-colors"
                                        title="Close this ticket">
                                        <x-heroicon-o-lock-closed class="w-4 h-4" />
                                        Close Ticket
                                    </button>
                                @elseif ($statusUp === 'CLOSED')
                                    <span class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-bold text-gray-600 bg-gray-100 border border-gray-200 rounded-lg uppercase tracking-wide">
                                        <x-heroicon-o-lock-closed class="w-3 h-3" />
                                        Closed
                                    </span>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="rounded-xl border-2 border-dashed border-gray-300 p-12 text-center">
                    {{-- Original SVG was a Document/File icon --}}
                    <x-heroicon-o-document-text class="mx-auto h-12 w-12 text-gray-400" />
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No tickets found</h3>
                    <p class="mt-1 text-sm text-gray-500">Try adjusting the filters above.</p>
                </div>
            @endforelse
        </div>

        @if(method_exists($tickets, 'links'))
            <div class="mt-6">
                {{ $tickets->links() }}
            </div>
        @endif
    </div>
</div>