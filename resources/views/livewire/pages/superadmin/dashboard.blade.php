<div class="min-h-screen bg-background">
    <main class="px-4 sm:px-6 py-6 space-y-6">

        {{-- Page header --}}
        <x-page-header title="Superadmin Analytics" subtitle="Interactive system insights for {{ $selectedYear }}">
            <x-slot:actions>
                <button wire:click="setFilter('all')"
                    class="px-4 py-2 text-sm font-medium bg-secondary text-secondary-foreground rounded-md border border-border hover:bg-accent transition-colors">
                    Reset View
                </button>
            </x-slot:actions>
        </x-page-header>

        {{-- Year Selector --}}
        <div class="bg-card border border-border rounded-lg p-4">
            <div class="flex items-center justify-between gap-4 flex-wrap">
                <div>
                    <p class="text-sm font-medium text-muted-foreground">Select Year</p>
                    <p class="text-xs text-muted-foreground/70">Viewing data for {{ $selectedYear }}</p>
                </div>
                <div class="flex flex-wrap gap-1.5">
                    @if(empty($availableYears))
                        <span class="text-sm text-muted-foreground">No data available</span>
                    @else
                        @foreach($availableYears as $year)
                            <button wire:click="setYear({{ $year }})"
                                class="px-3 py-1.5 rounded-md text-sm font-medium transition-colors duration-150
                                    {{ $selectedYear === $year
                                        ? 'bg-primary text-primary-foreground shadow-sm'
                                        : 'bg-secondary text-secondary-foreground hover:bg-accent' }}">
                                {{ $year }}
                            </button>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>

        {{-- KPI Cards --}}
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($stats as $s)
                @php
                    $isActive = $activeFilter === $s['key'];
                    $isUp = $s['direction'] === 'up';
                @endphp
                <div wire:click="setFilter('{{ $s['key'] }}')"
                    class="cursor-pointer bg-card border rounded-lg p-5 transition-all duration-150
                           hover:bg-accent/50
                           {{ $isActive ? 'border-foreground ring-1 ring-foreground' : 'border-border' }}">
                    <div class="flex justify-between items-start">
                        <p class="text-sm font-medium text-muted-foreground">{{ $s['label'] }}</p>
                        <span class="text-xs px-2 py-0.5 rounded-md font-medium
                            {{ $isUp ? 'text-success bg-success/10' : 'text-destructive bg-destructive/10' }}">
                            {{ $isUp ? '+' : '' }}{{ $s['trend'] }}%
                        </span>
                    </div>
                    <h2 class="text-2xl font-semibold mt-3 text-card-foreground tracking-tight">{{ number_format($s['value']) }}</h2>
                    <p class="mt-2 text-xs text-muted-foreground/60">Click to filter chart</p>
                </div>
            @endforeach
        </section>

        {{-- Chart --}}
        <div class="bg-card border border-border p-6 rounded-lg">
            <h3 class="text-sm font-semibold text-card-foreground mb-4">
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
    const CHART_COLORS = {
        'Room Bookings':    { border: '#2563eb', bg: 'rgba(37, 99, 235, 0.08)' },
        'Vehicle Bookings': { border: '#059669', bg: 'rgba(5, 150, 105, 0.08)' },
    };
    const FALLBACK_COLORS = ['#7c3aed', '#db2777'];

    function applyDatasetStyles(datasets) {
        return datasets.map((ds, i) => {
            const c = CHART_COLORS[ds.label] ?? {
                border: FALLBACK_COLORS[i] ?? '#374151',
                bg:     (FALLBACK_COLORS[i] ?? '#374151') + '14',
            };
            return {
                ...ds,
                borderColor:      c.border,
                backgroundColor:  c.bg,
                borderWidth:      2,
                tension:          0.35,
                fill:             false,
                pointRadius:      3,
                pointHoverRadius: 5,
            };
        });
    }

    function buildChart(labels, datasets) {
        const ctx = document.getElementById('chart');
        if (!ctx) return;

        if (window.dashChart && typeof window.dashChart.destroy === 'function') {
            window.dashChart.destroy();
        }

        window.dashChart = new Chart(ctx, {
            type: 'line',
            data: { labels, datasets: applyDatasetStyles(datasets) },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: { duration: 300 },
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            pointStyle: 'circle',
                            padding: 20,
                            font: { family: 'Inter', size: 12 }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'hsl(0 0% 9%)',
                        titleFont: { family: 'Inter', size: 12 },
                        bodyFont: { family: 'Inter', size: 12 },
                        padding: 10,
                        cornerRadius: 6,
                        callbacks: {
                            label: ctx => ctx.dataset.label + ': ' + ctx.parsed.y
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, font: { family: 'Inter', size: 11 } },
                        grid: { color: 'rgba(0,0,0,0.04)' },
                        title: { display: true, text: 'Bookings', font: { family: 'Inter', size: 12 } }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { family: 'Inter', size: 11 } },
                        title: { display: true, text: 'Month', font: { family: 'Inter', size: 12 } }
                    }
                }
            }
        });
    }

    function updateChart(labels, datasets) {
        if (!window.dashChart) {
            buildChart(labels, datasets);
            return;
        }
        window.dashChart.data.labels = labels;
        window.dashChart.data.datasets = applyDatasetStyles(datasets);
        window.dashChart.update('active');
    }

    document.addEventListener('DOMContentLoaded', () => {
        buildChart(@json($labels), @json($datasets));
    });

    document.addEventListener('livewire:init', () => {
        Livewire.on('chart-data-updated', ({ labels, datasets }) => {
            updateChart(labels, datasets);
        });
    });
</script>
@endpush
