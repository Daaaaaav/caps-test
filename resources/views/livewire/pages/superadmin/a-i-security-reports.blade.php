<div class="min-h-screen bg-gray-50" @if($autoRefresh) wire:poll.5s @endif>
    <main class="max-w-7xl mx-auto px-4 sm:px-6 py-8 space-y-8">
        {{-- HEADER --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Wazuh Security Reports</h1>
                <p class="text-sm text-gray-500">Live alerts from the Wazuh manager log</p>
            </div>
            <button wire:click="toggleAutoRefresh" 
                class="px-5 py-2.5 rounded-xl shadow-sm text-sm font-medium transition {{ $autoRefresh ? 'bg-green-600 text-white hover:bg-green-700' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50' }}">
                {{ $autoRefresh ? '● Live' : '○ Paused' }}
            </button>
        </div>

        {{-- STATS --}}
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($stats as $stat)
                <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm hover:shadow-lg transition">
                    <p class="text-sm font-medium text-gray-500">{{ $stat['label'] }}</p>
                    <h2 class="text-3xl font-bold mt-2 {{ [
                        'blue' => 'text-blue-600',
                        'red' => 'text-red-600',
                        'yellow' => 'text-yellow-600',
                        'green' => 'text-green-600',
                    ][$stat['color']] }}">{{ $stat['value'] }}</h2>
                </div>
            @endforeach
        </section>

        {{-- SOURCE STATUS --}}
        <section class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Data source</p>
                <p class="text-sm text-gray-900">
                    {{ $source_label }}
                    @if(isset($source) && $source)
                        <span class="text-gray-500">• {{ $source }}</span>
                    @endif
                </p>
            </div>
            <div class="text-sm text-gray-500">
                @if(isset($last_updated) && $last_updated)
                    Last updated {{ $last_updated }}
                @else
                    Source unavailable
                @endif
            </div>
        </section>

        {{-- SIEM CONNECTION --}}
        <section class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm">

            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">
                        SIEM Connection
                    </p>

                    <p class="text-sm text-gray-900 mt-1">
                        {{ $source_label }}

                        @if($source_host)
                            <span class="text-gray-500">
                                • {{ $source_host }}
                            </span>
                        @endif
                    </p>
                </div>

                <div>
                    @if($available)
                        <span class="px-3 py-1 rounded-full bg-green-100 text-green-700 text-xs font-medium">
                            Connected
                        </span>
                    @else
                        <span class="px-3 py-1 rounded-full bg-red-100 text-red-700 text-xs font-medium">
                            Offline
                        </span>
                    @endif
                </div>
            </div>

            {{-- DEBUG ENDPOINTS --}}
            @if(!empty($api_endpoints))
                <div class="mt-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-2">
                        Available API Endpoints
                    </p>

                    <div class="flex flex-wrap gap-2">
                        @foreach($api_endpoints as $endpoint)
                            <span class="px-3 py-1 rounded-lg bg-gray-100 text-gray-700 text-xs font-mono">
                                {{ $endpoint }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif

        </section>

        {{-- FILTER BUTTONS --}}
        <div class="flex flex-wrap gap-3">
            <button wire:click="setSeverity('all')"
                class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $selectedSeverity === 'all' ? 'bg-gray-900 text-white' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50' }}">
                All
            </button>
            <button wire:click="setSeverity('high')"
                class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $selectedSeverity === 'high' ? 'bg-red-600 text-white' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50' }}">
                🔴 High
            </button>
            <button wire:click="setSeverity('medium')"
                class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $selectedSeverity === 'medium' ? 'bg-yellow-500 text-white' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50' }}">
                🟡 Medium
            </button>
            <button wire:click="setSeverity('low')"
                class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $selectedSeverity === 'low' ? 'bg-green-600 text-white' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50' }}">
                🟢 Low
            </button>
        </div>

        {{-- ALERTS LIST --}}
        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
            @forelse($alerts as $alert)
                <div class="border-b border-gray-200 last:border-b-0 p-5 hover:bg-gray-50 transition">
                    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                        <div class="flex items-start gap-4">
                            <span class="px-3 py-1 rounded-full text-xs font-medium {{ [
                                'high' => 'bg-red-100 text-red-700',
                                'medium' => 'bg-yellow-100 text-yellow-700',
                                'low' => 'bg-green-100 text-green-700',
                            ][$alert['severity']] }}">
                                {{ $alert['severity_label'] }}
                            </span>
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="font-semibold text-gray-900">{{ $alert['title'] }}</p>
                                    @if($alert['rule_id'])
                                        <span class="text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-600">Rule {{ $alert['rule_id'] }}</span>
                                    @endif
                                </div>
                                <p class="text-sm text-gray-600 mt-1 break-words">{{ $alert['message'] }}</p>
                                <div class="mt-3 flex flex-wrap gap-2 text-xs text-gray-500">
                                    @foreach($alert['details'] as $detail)
                                        <span class="px-2 py-1 rounded-full bg-gray-100">{{ $detail }}</span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="text-sm text-gray-500 lg:text-right">
                            <div>{{ $alert['timestamp'] ?? 'Live entry' }}</div>
                            <div class="text-xs mt-1 uppercase tracking-wide">{{ $alert['severity'] }} severity</div>
                        </div>
                    </div>
                    <details class="mt-4">
                        <summary class="cursor-pointer text-sm text-blue-600 hover:text-blue-700">Show raw log</summary>
                        <pre class="mt-3 overflow-x-auto rounded-xl bg-gray-950 text-gray-100 text-xs leading-6 p-4 whitespace-pre-wrap">{{ $alert['raw'] }}</pre>
                    </details>
                </div>
            @empty
                <div class="p-8 text-center text-gray-500">
                    @if($available)
                        No alerts for this severity level
                    @else
                        No readable Wazuh alert source was found
                    @endif
                </div>
            @endforelse
        </div>
    </main>
</div>
