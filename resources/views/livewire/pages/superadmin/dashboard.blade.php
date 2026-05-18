<div class="min-h-screen bg-gray-50">
    <main class="max-w-7xl mx-auto px-4 sm:px-6 py-8 space-y-8">
        {{-- HEADER --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Superadmin Analytics</h1>
                <p class="text-sm text-gray-500">Interactive system insights</p>
            </div>
            <button wire:click="setFilter('all')"
                class="px-5 py-2.5 bg-gray-900 text-white rounded-xl shadow-sm hover:bg-gray-800 transition">
                Reset View
            </button>
        </div>

        {{-- YEAR SELECTOR --}}
        <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-sm font-medium text-gray-500">Select Year</p>
                    <p class="text-xs text-gray-400">Viewing data for {{ $selectedYear }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    @if(empty($availableYears))
                        <span class="text-sm text-gray-400">No data available</span>
                    @else
                        @foreach($availableYears as $year)
                            <button wire:click="setYear({{ $year }})"
                                class="px-4 py-2 rounded-lg font-medium transition-all duration-200
                                    {{ $selectedYear === $year ? 'bg-gray-900 text-white shadow-md' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                                {{ $year }}
                            </button>
                        @endforeach
                    @endif
                </div>
            </div>
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
                           {{ $isActive ? 'ring-2 ring-gray-900' : '' }}">
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
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                Booking Trends — {{ $selectedYear }}
            </h3>
            <div wire:ignore style="position: relative; height: 400px;">
                <canvas id="chart"></canvas>
            </div>
        </div>
    </main>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    function buildChart() {
        const ctx = document.getElementById('chart');
        if (!ctx) return;

        const labels   = @json($labels);
        const datasets = @json($datasets);

        // Apply monochrome colors regardless of what PHP passes
        const colors = ['#1f2937', '#6b7280'];
        datasets.forEach((ds, i) => {
            ds.borderColor       = colors[i] ?? '#1f2937';
            ds.backgroundColor   = (colors[i] ?? '#1f2937') + '1a'; // 10% opacity
            ds.borderWidth       = 2.5;
            ds.tension           = 0.4;
            ds.fill              = false;
            ds.pointRadius       = 4;
            ds.pointHoverRadius  = 6;
        });

        if (window.dashChart && typeof window.dashChart.destroy === 'function') {
            window.dashChart.destroy();
        }

        window.dashChart = new Chart(ctx, {
            type: 'line',
            data: { labels, datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: { duration: 400 },
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: true, position: 'top' },
                    tooltip: {
                        callbacks: {
                            label: ctx => ctx.dataset.label + ': ' + ctx.parsed.y
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 },
                        title: { display: true, text: 'Bookings' }
                    },
                    x: {
                        title: { display: true, text: 'Month' }
                    }
                }
            }
        });
    }

    document.addEventListener('DOMContentLoaded', () => buildChart());

    document.addEventListener('livewire:init', () => {
        Livewire.hook('morph.updated', () => {
            setTimeout(() => buildChart(), 100);
        });
    });
</script>
@endpush
