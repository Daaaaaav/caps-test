<div class="min-h-screen bg-gray-50" wire:poll.5s>
    <main class="max-w-7xl mx-auto px-4 sm:px-6 py-8 space-y-8">
        {{-- HEADER --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">AI Security Reports</h1>
                <p class="text-sm text-gray-500">Real-time threat detection & monitoring</p>
            </div>
            <button wire:click="toggleAutoRefresh" 
                class="px-5 py-2.5 rounded-xl shadow-sm text-sm font-medium transition {{ $autoRefresh ? 'bg-green-600 text-white hover:bg-green-700' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50' }}">
                {{ $autoRefresh ? '● Live' : '○ Paused' }}
            </button>
        </div>

        {{-- STATS --}}
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($stats as $stat)
                @php
                    $colors = [
                        'blue' => 'text-blue-600',
                        'red' => 'text-red-600',
                        'yellow' => 'text-yellow-600',
                        'green' => 'text-green-600',
                    ];
                @endphp
                <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm hover:shadow-lg transition">
                    <p class="text-sm font-medium text-gray-500">{{ $stat['label'] }}</p>
                    <h2 class="text-3xl font-bold mt-2 {{ $colors[$stat['color']] }}">{{ $stat['value'] }}</h2>
                </div>
            @endforeach
        </section>

        {{-- FILTER BUTTONS --}}
        <div class="flex flex-wrap gap-3">
            <button wire:click="setSeverity('all')" 
                class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $selectedSeverity === 'all' ? 'bg-blue-600 text-white' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50' }}">
                All
            </button>
            <button wire:click="setSeverity('high')" 
                class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $selectedSeverity === 'high' ? 'bg-red-600 text-white' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50' }}">
                High
            </button>
            <button wire:click="setSeverity('medium')" 
                class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $selectedSeverity === 'medium' ? 'bg-yellow-600 text-white' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50' }}">
                Medium
            </button>
            <button wire:click="setSeverity('low')" 
                class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $selectedSeverity === 'low' ? 'bg-green-600 text-white' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50' }}">
                Low
            </button>
        </div>

        {{-- ALERTS LIST --}}
        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm divide-y divide-gray-200">
            @forelse($alerts as $alert)
                @php
                    $severityColors = [
                        'high' => 'bg-red-100 text-red-700',
                        'medium' => 'bg-yellow-100 text-yellow-700',
                        'low' => 'bg-green-100 text-green-700',
                    ];
                @endphp
                <div class="p-5 hover:bg-gray-50 transition">
                    <div class="flex items-start gap-4">
                        <span class="px-3 py-1 rounded-full text-xs font-medium {{ $severityColors[$alert['severity']] }}">
                            {{ ucfirst($alert['severity']) }}
                        </span>
                        <div class="flex-1">
                            <p class="font-medium text-gray-900">{{ $alert['message'] }}</p>
                            <p class="text-sm text-gray-500 mt-1">{{ $alert['time'] }}</p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-gray-500">
                    No alerts for this severity level
                </div>
            @endforelse
        </div>
    </main>
</div>
