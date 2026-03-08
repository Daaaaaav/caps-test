<div class="bg-gray-50">

    <!-- Download overlay (shown during PDF preparation) -->
    <div id="agentDownloadOverlay" class="fixed inset-0 z-[100] hidden">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
        <div class="relative h-full w-full flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-xs text-center">
                <div class="mx-auto mb-3 h-10 w-10 animate-spin rounded-full border-4 border-gray-200 border-t-gray-900"></div>
                <p class="font-semibold text-gray-900">Menyiapkan PDF…</p>
                <p class="text-xs text-gray-500 mt-1">Tunggu sampai dialog download muncul.</p>
                <button id="agentHideOverlay" type="button" class="mt-4 text-xs text-gray-600 underline">Sembunyikan</button>
            </div>
        </div>
    </div>

    @php
        // reusable design classes
        $card = 'bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden';

        // Base chip style (no explicit bg/text colors here)
        $chip = 'inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-medium ring-1 ring-inset';

        // Priority colors
        $priorityColors = [
            'Low' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
            'Medium' => 'bg-amber-50 text-amber-700 ring-amber-200',
            'High' => 'bg-rose-50 text-rose-700 ring-rose-200',
        ];

        // Status colors
        $statusColors = [
            'Open' => 'bg-sky-50 text-sky-700 ring-sky-200',
            'In Progress' => 'bg-indigo-50 text-indigo-700 ring-indigo-200',
            'Resolved' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
            'Closed' => 'bg-gray-100 text-gray-700 ring-gray-200',
        ];
    @endphp

    <script>
        // Function to set bar widths after page loads and after Livewire updates
        function applyFlexBasis() {
            document.querySelectorAll('[data-flex-basis]').forEach(el => {
                el.style.flex = '0 0 ' + el.dataset.flexBasis + '%';
            });
        }

        document.addEventListener('DOMContentLoaded', applyFlexBasis);
        window.addEventListener('livewire:updated', applyFlexBasis);
    </script>

    <main class="px-4 sm:px-6 py-6 space-y-5">

        {{-- Header --}}
        <header
            class="rounded-2xl bg-gradient-to-r from-gray-900 to-black text-white shadow-xl px-5 py-4 flex items-center gap-4">
            <div
                class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center border border-white/20 shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6V4m0 16v-2m0-10v2m0 6v2M6 12H4m16 0h-2m-10 0h2m6 0h2M9 17l-2 2M15 7l2-2M7 7l-2-2M17 17l2 2" />
                </svg>
            </div>
            <div class="min-w-0">
                <h2 class="text-base sm:text-lg font-semibold truncate">Agent Report</h2>
                <p class="text-xs text-white/80 truncate">Overview of Agents and Their Assigned Support Tickets.</p>
            </div>
        </header>

        {{-- AHT Summary: Avg resolution time, Fastest agent, Slowest agent --}}
        <section class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @php $sum = $ahtSummary ?? null; @endphp

            {{-- Overall Avg Resolution Time --}}
            <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Avg Resolution Time</p>
                        <p class="text-2xl font-bold text-gray-900">
                            {{ is_null($sum['overall_avg'] ?? null) ? '—' : number_format($sum['overall_avg'], 2) }} jam
                        </p>
                        <p class="text-xs text-gray-500 mt-1">Based on {{ $sum['overall_count'] ?? 0 }} resolved tickets</p>
                    </div>
                    <div class="p-3 bg-gray-100 rounded-lg">
                        <x-heroicon-o-clock class="w-6 h-6 text-gray-700" />
                    </div>
                </div>
            </div>

            {{-- Fastest Agent --}}
            <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Fastest Agent</p>
                        <p class="text-lg font-semibold text-gray-900">{{ $sum['fastest']['full_name'] ?? '—' }}</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">
                            {{ is_null($sum['fastest']['avg_hours'] ?? null) ? '—' : number_format($sum['fastest']['avg_hours'], 2) }} jam
                        </p>
                        <p class="text-xs text-gray-500 mt-1">Handled: {{ $sum['fastest']['count'] ?? 0 }}</p>
                    </div>
                    <div class="p-3 bg-emerald-100 rounded-lg">
                        <x-heroicon-o-arrow-trending-up class="w-6 h-6 text-emerald-700" />
                    </div>
                </div>
            </div>

            {{-- Slowest Agent --}}
            <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Slowest Agent</p>
                        <p class="text-lg font-semibold text-gray-900">{{ $sum['slowest']['full_name'] ?? '—' }}</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">
                            {{ is_null($sum['slowest']['avg_hours'] ?? null) ? '—' : number_format($sum['slowest']['avg_hours'], 2) }} jam
                        </p>
                        <p class="text-xs text-gray-500 mt-1">Handled: {{ $sum['slowest']['count'] ?? 0 }}</p>
                    </div>
                    <div class="p-3 bg-rose-100 rounded-lg">
                        <x-heroicon-o-exclamation-triangle class="w-6 h-6 text-rose-700" />
                    </div>
                </div>
            </div>
        </section>

        {{-- Statistics Card --}}
        <section class="{{ $card }}">
            <div class="px-5 py-4 border-b border-gray-200">
                <h3 class="text-base font-semibold text-gray-900 mb-4">Agents by Ticket Count</h3>

                @if($topAgents->isEmpty())
                    <p class="text-gray-500 text-sm">No agents found.</p>
                @else
                    @php
                        $max = $topAgents
                            ->map(fn($a) => $allTicketStatsDetailed[$a->user_id]['total'] ?? 0)
                            ->max() ?: 1;
                    @endphp

                    <div class="space-y-4">
                        @foreach($topAgents as $agent)
                            @php
                                $stats = $allTicketStatsDetailed[$agent->user_id] ?? [];

                                $total = $stats['total'] ?? 0;
                                $open = $stats['Open'] ?? 0;
                                $resolved = $stats['Resolved'] ?? 0;
                                $progress = $stats['IN_PROGRESS'] ?? 0;
                                $closed = $stats['Closed'] ?? 0;

                                // SLA COUNTS
                                $agentTickets = $allTickets->where('user_id', $agent->user_id);

                                $slaCounts = [
                                    'Open' => [
                                        'ok' => $agentTickets->where('status', 'OPEN')->where('sla_state.state', 'ok')->count(),
                                        'expired' => $agentTickets->where('status', 'OPEN')->where('sla_state.state', 'expired')->count(),
                                    ],
                                    'IN_PROGRESS' => [
                                        'ok' => $agentTickets->where('status', 'IN_PROGRESS')->where('sla_state.state', 'ok')->count(),
                                        'expired' => $agentTickets->where('status', 'IN_PROGRESS')->where('sla_state.state', 'expired')->count(),
                                    ],
                                    'Resolved' => [
                                        'ok' => $agentTickets->where('status', 'RESOLVED')->where('sla_state.state', 'ok')->count(),
                                        'expired' => $agentTickets->where('status', 'RESOLVED')->where('sla_state.state', 'expired')->count(),
                                    ],
                                    'Closed' => [
                                        'ok' => $agentTickets->where('status', 'CLOSED')->where('sla_state.state', 'ok')->count(),
                                        'expired' => $agentTickets->where('status', 'CLOSED')->where('sla_state.state', 'expired')->count(),
                                    ],
                                ];

                                $barWidth = $max > 0 ? ($total / $max) * 100 : 0;
                            @endphp

                            <div class="space-y-1">

                                {{-- Agent name + total --}}
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-900 font-medium">{{ $agent->full_name }}</span>
                                    <span class="text-sm text-gray-900 font-semibold">{{ $total }} tickets</span>
                                </div>

                                {{-- Total bar --}}
                                <div class="w-full h-4 bg-gray-200 rounded overflow-hidden">
                                    <div x-data="{ width: {{ $barWidth }} }" :style="'width: ' + width + '%'"
                                        class="bg-gradient-to-r from-gray-900 to-black h-4">
                                    </div>
                                </div>

                                {{-- Status counts + SLA pills --}}
                                <div class="flex flex-wrap gap-4 text-xs text-gray-600 mt-2">

                                    {{-- OPEN --}}
                                    <div class="p-1 bg-white shadow rounded-lg border border-gray-200 flex items-center gap-1">
                                        Open: <span class="font-semibold">{{ $open }}</span>

                                        @php
                                            $ok = $slaCounts['Open']['ok'];
                                            $exp = $slaCounts['Open']['expired'];
                                        @endphp

                                        @if ($ok > 0)
                                            <span
                                                class="px-1.5 py-0.5 ml-1 text-white rounded bg-green-600 text-[10px]">{{ $ok }}</span>
                                        @endif

                                        @if ($exp > 0)
                                            <span
                                                class="px-1.5 py-0.5 ml-1 text-white rounded bg-red-600 text-[10px]">{{ $exp }}</span>
                                        @endif
                                    </div>

                                    {{-- IN PROGRESS --}}
                                    <div class="p-1 bg-white shadow rounded-lg border border-gray-200 flex items-center gap-1">
                                        In Progress: <span class="font-semibold">{{ $progress }}</span>

                                        @php
                                            $ok = $slaCounts['IN_PROGRESS']['ok'];
                                            $exp = $slaCounts['IN_PROGRESS']['expired'];
                                        @endphp

                                        @if ($ok > 0)
                                            <span
                                                class="px-1.5 py-0.5 ml-1 text-white rounded bg-green-600 text-[10px]">{{ $ok }}</span>
                                        @endif

                                        @if ($exp > 0)
                                            <span
                                                class="px-1.5 py-0.5 ml-1 text-white rounded bg-red-600 text-[10px]">{{ $exp }}</span>
                                        @endif
                                    </div>

                                    {{-- RESOLVED --}}
                                    <div class="p-1 bg-white shadow rounded-lg border border-gray-200 flex items-center gap-1">
                                        Resolved: <span class="font-semibold">{{ $resolved }}</span>

                                        @php
                                            $ok = $slaCounts['Resolved']['ok'];
                                            $exp = $slaCounts['Resolved']['expired'];
                                        @endphp

                                        @if ($ok > 0)
                                            <span
                                                class="px-1.5 py-0.5 ml-1 text-white rounded bg-green-600 text-[10px]">{{ $ok }}</span>
                                        @endif

                                        @if ($exp > 0)
                                            <span
                                                class="px-1.5 py-0.5 ml-1 text-white rounded bg-red-600 text-[10px]">{{ $exp }}</span>
                                        @endif
                                    </div>

                                    {{-- CLOSED --}}
                                    <div class="p-1 bg-white shadow rounded-lg border border-gray-200 flex items-center gap-1">
                                        Closed: <span class="font-semibold"> {{ $closed }}</span>

                                        @php
                                            $ok = $slaCounts['Closed']['ok'];
                                            $exp = $slaCounts['Closed']['expired'];
                                        @endphp

                                        @if ($ok > 0)
                                            <span
                                                class="px-1.5 py-0.5 ml-1 text-white rounded bg-green-600 text-[10px]">{{ $ok }}</span>
                                        @endif

                                        @if ($exp > 0)
                                            <span
                                                class="px-1.5 py-0.5 ml-1 text-white rounded bg-red-600 text-[10px]">{{ $exp }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>

        {{-- Main Agent Card (GRID) --}}
        <section class="{{ $card }}">

            {{-- Header --}}
            <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">

                <h3 class="text-base font-semibold text-gray-900">Agent List</h3>

                {{-- Right side: search + download --}}
                <div class="flex items-center gap-3">

                    {{-- Search --}}
                    <input type="text" wire:model.live.debounce.100ms="search"
                        placeholder="Search agent by name or ID..." class="w-64 rounded-lg bg-white/50 border border-gray-200 px-3 py-2 text-gray-500 text-sm
                                focus:ring-2 focus:ring-gray-900/10 focus:outline-none">

                    {{-- Download Button --}}
                    <button id="downloadReportBtn" wire:click="downloadReport" wire:loading.attr="disabled" wire:target="downloadReport"
                        class="px-3 py-2 rounded-lg bg-gradient-to-r from-gray-900 to-black text-white text-sm shadow inline-flex items-center gap-2 cursor-pointer
                            disabled:opacity-50 disabled:cursor-not-allowed disabled:bg-gray-400 disabled:text-gray-200">
                        <svg id="agentBtnSpinner" class="hidden h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity=".25" stroke-width="4"></circle>
                            <path d="M22 12a10 10 0 0 1-10 10" stroke="currentColor" stroke-width="4"></path>
                        </svg>
                        <svg id="agentBtnIcon" class="h-4 w-4" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 3v12m9-9-9 9-9-9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span id="agentBtnLabel">Download Report</span>
                    </button>
                </div>
            </div>

            {{-- Search Chip --}}
            @if(!empty($search))
                <div class="px-5 py-2 bg-white border-b border-gray-200 flex items-center gap-2">
                    <span class="text-sm text-gray-800">
                        Search:
                        <span class="px-2 py-1 bg-gray-100 rounded-lg text-gray-800 font-medium">
                            {{ $search }}
                        </span>
                    </span>
                </div>
            @endif

            {{-- Agent Grid List --}}
            <div class="grid grid-rows-3 grid-cols-3 sm:grid-cols-2 lg:grid-cols-3 gap-4 px-5 py-4">
                @forelse ($agents as $agent)
                    <div wire:key="agent-{{ $agent->user_id }}"
                        class="cursor-pointer rounded-xl border border-gray-200 bg-white shadow-sm hover:shadow-md transition p-4"
                        wire:click="openToast('{{ $agent->user_id }}')" role="button" tabindex="0"
                        onkeypress="if(event.key === 'Enter') { @this.openToast('{{ $agent->user_id }}') }">

                        <div class="flex items-center gap-3">
                            {{-- Agent Avatar Icon --}}
                            <div
                                class="w-12 h-12 rounded-full bg-gray-900 text-white flex items-center justify-center font-semibold text-lg">
                                {{ strtoupper(substr($agent->full_name, 0, 1)) }}
                            </div>

                            <div class="min-w-0">
                                <p class="font-semibold text-sm text-gray-900 truncate">{{ $agent->full_name }}</p>
                                <p class="text-xs text-gray-500 truncate">
                                    {{ $agent->company_name ?? '-' }} • {{ $agent->department_name ?? '-' }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-3 flex items-center justify-between">
                            <div>
                                <p class="text-xs text-gray-500 mb-1">Assigned Tickets</p>
                                <div class="text-xl font-bold text-gray-900">
                                    {{ $agent->tickets_count ?? ($agent->tickets?->count() ?? 0) }}
                                </div>
                            </div>

                            {{-- Quick status summary --}}
                            <div class="text-right text-xs text-gray-500">
                                <div>Open: {{ $allTicketStatsDetailed[$agent->user_id]['Open'] ?? 0 }}</div>
                                <div>In Progress: {{ $allTicketStatsDetailed[$agent->user_id]['IN_PROGRESS'] ?? 0 }}</div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-4 text-gray-500 text-sm">
                        No agents found.
                    </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            @if($agents->hasPages())
                <div class="px-5 py-4 bg-white border-t border-gray-100">
                    <div class="flex justify-center">
                        {{ $agents->links() }}
                    </div>
                </div>
            @endif

        </section>

        {{-- Center Popup Modal --}}
        @if($openAgent)
            @php
                $selectedAgent = $agents->where('user_id', $openAgent)->first() ?? null;
            @endphp

            @if($selectedAgent)
                <div class="fixed inset-0 z-[999] flex items-center justify-center" aria-modal="true" role="dialog">
                    {{-- Overlay --}}
                    <div class="absolute inset-0 bg-black/50" wire:click="closeToast" aria-hidden="true"></div>

                    {{-- Modal container --}}
                    <div class="relative w-[95%] max-w-2xl mx-auto bg-white rounded-2xl shadow-xl overflow-hidden">

                        {{-- Modal header --}}
                        <div class="px-4 py-3 bg-gray-900 text-white flex items-center justify-between">
                            <div class="min-w-0">
                                <h4 class="text-sm font-semibold truncate">Tickets for {{ $selectedAgent->full_name }}</h4>
                                <p class="text-xs text-white/80 truncate">Showing assigned tickets and SLA status</p>
                            </div>

                            <div class="flex items-center gap-2">
                                <button wire:click="closeToast" class="p-2 rounded-md hover:bg-white/5">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        {{-- Modal body: tickets --}}
                        <div class="max-h-[60vh] overflow-auto p-4 space-y-3 bg-gray-50">

                            @if(!empty($selectedAgent->tickets) && $selectedAgent->tickets->isNotEmpty())
                                @foreach($selectedAgent->tickets as $ticket)
                                    @php
                                        $slaData = $ticket->sla_state ?? [];
                                        $slaState = $slaData['state'] ?? null;
                                        $slaLabel = $slaData['label'] ?? null;
                                        $slaClasses = $slaData['classes'] ?? '';
                                        $hoursElapsed = $slaData['hours_elapsed'] ?? 0;
                                        $ticketStatus = ucwords(strtolower(str_replace('_', ' ', trim((string) ($ticket->status ?? '')))));
                                        $ticketPriority = ucwords(strtolower(str_replace('_', ' ', trim((string) ($ticket->priority ?? '')))));
                                    @endphp

                                    <a href="{{ url('/admin/tickets/' . $ticket->ulid) }}" class="block">
                                        <div class="p-3 bg-white rounded-lg border hover:shadow transition">
                                            <div class="flex items-start justify-between gap-3">
                                                <div class="min-w-0">
                                                    <div class="flex items-center gap-2">
                                                        <div
                                                            class="flex-shrink-0 px-3 py-1 bg-white rounded shadow text-gray-700 text-sm font-mono font-semibold">
                                                            #{{ $ticket->ticket_id }}
                                                        </div>

                                                        <div class="truncate">
                                                            <div class="font-semibold text-sm text-gray-900 truncate max-w-[40ch]">
                                                                {{ $ticket->subject ?? 'No Subject' }}</div>
                                                            <div class="mt-2 flex flex-wrap gap-2 text-[10px]">
                                                                @if(!empty($ticket->status))
                                                                    <span 
                                                                        class="{{ $chip }} {{ $statusColors[$ticketStatus] ?? 'bg-gray-100 text-gray-700 ring-gray-200' }} capitalize">
                                                                        <span>{{ $ticketStatus }}</span>
                                                                    </span>
                                                                @endif

                                                                @if(!empty($ticket->priority))
                                                                    <span
                                                                        class="{{ $chip }} {{ $priorityColors[$ticketPriority] ?? 'bg-gray-100 text-gray-700 ring-gray-200' }} capitalize">
                                                                        <span>{{ $ticketPriority }}</span>
                                                                    </span>
                                                                @endif

                                                                @if($slaState)
                                                                    <span
                                                                        class="px-2 py-0.5 rounded {{ $slaClasses }} text-[11px] font-semibold">{{ $slaLabel }}</span>
                                                                @endif

                                                                {{-- Time elapsed --}}
                                                                @php $h = $hoursElapsed ?? 0; @endphp
                                                                @if ($h > 0)
                                                                    <div class="px-2 py-0.5 bg-gray-100 rounded text-[10px] text-gray-700">
                                                                        @if ($h < 1)
                                                                            Updated: {{ floor($h * 60) }}m ago
                                                                        @elseif ($h < 24)
                                                                            Updated: {{ floor($h) }}h ago
                                                                        @elseif ($h < 24 * 30)
                                                                            Updated: {{ floor($h / 24) }}d ago
                                                                        @elseif ($h < 24 * 30 * 12)
                                                                            Updated: {{ floor($h / (24 * 30)) }}mo ago
                                                                        @else
                                                                            Updated: {{ floor($h / (24 * 365)) }}y ago
                                                                        @endif
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            @else
                                <div class="p-4 text-center text-gray-500">No tickets assigned to this agent.</div>
                            @endif

                        </div>

                    </div>
                </div>
            @endif
        @endif

    </main>

    <script>
        const dlBtn = document.getElementById('downloadReportBtn');
        const dlOverlay = document.getElementById('agentDownloadOverlay');
        const btnSpinner = document.getElementById('agentBtnSpinner');
        const btnLabel = document.getElementById('agentBtnLabel');

        function setDownloading(state) {
            if (!dlBtn) return;
            if (state) {
                dlBtn.disabled = true;
                btnSpinner?.classList.remove('hidden');
                btnLabel && (btnLabel.textContent = 'Menyiapkan…');
                dlOverlay?.classList.remove('hidden');
            } else {
                dlBtn.disabled = false;
                btnSpinner?.classList.add('hidden');
                btnLabel && (btnLabel.textContent = 'Download Report');
                dlOverlay?.classList.add('hidden');
            }
        }

        document.getElementById('agentHideOverlay')?.addEventListener('click', () => setDownloading(false));

        dlBtn?.addEventListener('click', () => setDownloading(true));

        // Hide overlay when Livewire request ends or on error
        window.addEventListener('livewire:request-end', () => setTimeout(() => setDownloading(false), 1000));
        window.addEventListener('livewire:error', () => setDownloading(false));
    </script>

</div>