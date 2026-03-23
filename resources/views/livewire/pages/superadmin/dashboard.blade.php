<div class="min-h-screen bg-gray-50">
    <main class="max-w-7xl mx-auto px-4 sm:px-6 py-8 space-y-8">
        {{-- HEADER --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Superadmin Analytics</h1>
                <p class="text-sm text-gray-500">Interactive system insights</p>
            </div>
            <button wire:click="setFilter('all')" 
                class="px-5 py-2.5 bg-blue-600 text-white rounded-xl shadow-sm hover:bg-blue-700 transition">
                Reset View
            </button>
        </div>

        {{-- KPI CARDS --}}
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($stats as $s)
                @php
                    $isActive = $activeFilter === $s['key'];
                    $isUp = $s['direction'] === 'up';
                    $color = $isUp ? 'text-emerald-600 bg-emerald-50' : 'text-red-600 bg-red-50';
                @endphp
                <div wire:click="setFilter('{{ $s['key'] }}')"
                    class="cursor-pointer bg-white border border-gray-200 rounded-2xl p-5 shadow-sm transition-all duration-300
                           hover:shadow-lg hover:-translate-y-1
                           {{ $isActive ? 'ring-2 ring-blue-500' : '' }}">
                    <div class="flex justify-between items-start">
                        <p class="text-sm font-medium text-gray-500">{{ $s['label'] }}</p>
                        <span class="text-xs px-2 py-1 rounded-full font-medium {{ $color }}">
                            {{ $isUp ? '+' : '' }}{{ $s['trend'] }}%
                        </span>
                    </div>
                    <h2 class="text-3xl font-bold mt-4 text-gray-900">{{ number_format($s['value']) }}</h2>
                    <div class="mt-3 text-xs text-gray-400">Click to filter</div>
                </div>
            @endforeach
        </section>

        {{-- CHART --}}
        <div class="bg-white border border-gray-200 p-6 rounded-2xl shadow-sm">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Booking Trends</h3>
            <canvas id="chart"></canvas>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @script
    <script>
        function buildChart() {
            const ctx = document.getElementById('chart').getContext('2d');
            const labels = @json($labels);
            const datasets = @json($datasets);

            if (window.chart) window.chart.destroy();

            window.chart = new Chart(ctx, {
                type: 'line',
                data: { labels, datasets },
                options: {
                    responsive: true,
                    animation: { duration: 500 }
                }
            });
        }

        document.addEventListener('DOMContentLoaded', buildChart);
        document.addEventListener('livewire:updated', buildChart);
    </script>
    @endscript
</div>
