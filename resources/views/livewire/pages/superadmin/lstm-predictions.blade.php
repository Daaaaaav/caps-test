<div class="min-h-screen bg-[#f5f7f2]">
    <main class="max-w-7xl mx-auto px-4 sm:px-6 py-8 space-y-8">

        {{-- HEADER --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-[#2d3a24]">{{ __('app.lstm_title') }}</h1>
                <p class="text-sm text-[#7a8f6a] mt-1">
                    {{ __('app.lstm_subtitle') }}
                </p>
            </div>
            <span class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm font-medium
                {{ $isLSTMAvailable ? 'bg-green-100 text-green-700' : 'bg-[#eef1e8] text-[#5a6e4a]' }}">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                {{ $isLSTMAvailable ? __('app.lstm_model') : __('app.statistical_model') }}
            </span>
        </div>

        {{-- CONTROLS --}}
        <div class="bg-white border border-[#d4dfc8] rounded-2xl p-6 shadow-sm">
            <div>
                <label class="block text-sm font-medium text-[#4E653D] mb-2">{{ __('app.forecast_period') }}</label>
                <div class="flex gap-2 max-w-xs">
                    <button wire:click="setForecastDays(7)"
                        class="flex-1 px-4 py-2 rounded-lg text-sm font-medium transition {{ $forecastDays === 7 ? 'bg-[#4A2F24] text-white' : 'bg-[#eef1e8] text-[#4E653D] hover:bg-[#dde4d4]' }}">
                        7 Days
                    </button>
                    <button wire:click="setForecastDays(14)"
                        class="flex-1 px-4 py-2 rounded-lg text-sm font-medium transition {{ $forecastDays === 14 ? 'bg-[#4A2F24] text-white' : 'bg-[#eef1e8] text-[#4E653D] hover:bg-[#dde4d4]' }}">
                        14 Days
                    </button>
                    <button wire:click="setForecastDays(21)"
                        class="flex-1 px-4 py-2 rounded-lg text-sm font-medium transition {{ $forecastDays === 21 ? 'bg-[#4A2F24] text-white' : 'bg-[#eef1e8] text-[#4E653D] hover:bg-[#dde4d4]' }}">
                        21 Days
                    </button>
                </div>
            </div>
        </div>

        @if(!empty($predictions))

            {{-- STATS CARDS --}}
            <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($stats as $stat)
                    <div class="bg-white border border-[#d4dfc8] rounded-2xl p-5 shadow-sm hover:shadow-lg transition">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-sm font-medium text-[#7a8f6a]">{{ $stat['label'] }}</p>
                            <div class="w-10 h-10 rounded-lg bg-[#eef1e8] text-[#4E653D] flex items-center justify-center">
                                <x-dynamic-component :component="'heroicon-o-' . $stat['icon']" class="w-5 h-5" />
                            </div>
                        </div>
                        <h2 class="text-3xl font-bold text-[#2d3a24]">{{ $stat['value'] }}</h2>
                    </div>
                @endforeach
            </section>

            {{-- DAILY PREDICTIONS CHART --}}
            <div class="bg-white border border-[#d4dfc8] p-6 rounded-2xl shadow-sm">
                <div class="mb-4">
                    <h3 class="text-lg font-semibold text-[#2d3a24]">{{ __('app.daily_predictions') }}</h3>
                    <p class="text-sm text-[#7a8f6a] mt-1">
                        @if($rmse > 0) RMSE: {{ number_format($rmse, 4) }} &middot; @endif
                        Model: <span class="font-medium capitalize">{{ $isLSTMAvailable ? __('app.lstm_model') : __('app.statistical_model') }}</span>
                    </p>
                </div>
                <div wire:ignore style="position: relative; height: 400px;">
                    <canvas id="dailyPredictionsChart"></canvas>
                </div>
            </div>

            {{-- WEEKLY SUMMARY CHART (21-day only) --}}
            @if($weeklyData)
                <div class="bg-white border border-[#d4dfc8] p-6 rounded-2xl shadow-sm">
                    <div class="mb-4">
                        <h3 class="text-lg font-semibold text-[#2d3a24]">{{ __('app.weekly_summary') }}</h3>
                        <p class="text-sm text-[#7a8f6a] mt-1">{{ __('app.weekly_summary_sub') }}</p>
                    </div>
                    <div wire:ignore style="position: relative; height: 300px;">
                        <canvas id="weeklySummaryChart"></canvas>
                    </div>
                </div>
            @endif

            {{-- PREDICTIONS TABLE --}}
            <div class="bg-white border border-[#d4dfc8] rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b bg-[#f0f4eb]">
                    <h3 class="font-semibold text-[#2d3a24]">{{ __('app.detailed_predictions') }}</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-[#f0f4eb] text-[#7a8f6a] uppercase text-xs border-b">
                            <tr>
                                <th class="px-6 py-3 text-left font-medium">{{ __('app.date') }}</th>
                                <th class="px-6 py-3 text-left font-medium">{{ __('app.day') }}</th>
                                <th class="px-6 py-3 text-right font-medium">{{ __('app.predicted') }}</th>
                                <th class="px-6 py-3 text-right font-medium">{{ __('app.lower_bound') }}</th>
                                <th class="px-6 py-3 text-right font-medium">{{ __('app.upper_bound') }}</th>
                                <th class="px-6 py-3 text-right font-medium">{{ __('app.confidence') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#d4dfc8]">
                            @foreach($predictions as $pred)
                                <tr class="hover:bg-[#f0f4eb]">
                                    <td class="px-6 py-4 text-[#2d3a24] font-medium">{{ $pred['date'] }}</td>
                                    <td class="px-6 py-4 text-[#5a6e4a]">{{ date('l', strtotime($pred['date'])) }}</td>
                                    <td class="px-6 py-4 text-right text-[#2d3a24] font-semibold">{{ number_format($pred['predicted'], 1) }}</td>
                                    <td class="px-6 py-4 text-right text-[#5a6e4a]">{{ number_format($pred['lower_bound'], 1) }}</td>
                                    <td class="px-6 py-4 text-right text-[#5a6e4a]">{{ number_format($pred['upper_bound'], 1) }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <span class="px-2 py-1 text-xs rounded-full font-medium
                                            {{ $pred['confidence'] >= 0.8 ? 'bg-green-100 text-green-700' : ($pred['confidence'] >= 0.6 ? 'bg-[#eef1e8] text-[#4E653D]' : 'bg-[#eef1e8] text-[#7a8f6a]') }}">
                                            {{ number_format($pred['confidence'] * 100, 1) }}%
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        @else
            <div class="bg-white border border-[#d4dfc8] rounded-2xl p-12 text-center shadow-sm">
                <svg class="w-12 h-12 text-[#b5c4a5] mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                <p class="text-[#7a8f6a] font-medium">{{ __('app.no_prediction_data') }}</p>
                <p class="text-sm text-[#9aaa8a] mt-1">{{ __('app.select_different_period') }}</p>
            </div>
        @endif

    </main>
</div>

@if(!empty($predictions))
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => initCharts());

    document.addEventListener('livewire:init', () => {
        Livewire.hook('morph.updated', () => setTimeout(() => initCharts(), 150));
    });

    function initCharts() {
        buildDailyChart();
        @if($weeklyData) buildWeeklyChart(); @endif
    }

    function buildDailyChart() {
        const ctx = document.getElementById('dailyPredictionsChart');
        if (!ctx) return;

        if (window.dailyChart && typeof window.dailyChart.destroy === 'function') window.dailyChart.destroy();

        window.dailyChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($dailyLabels),
                datasets: [
                    {
                        label: 'Predicted',
                        data: @json($dailyPredicted),
                        borderColor: '#4E653D',
                        backgroundColor: 'rgba(78, 101, 61, 0.1)',
                        borderWidth: 3, fill: false, tension: 0.4, pointRadius: 4, pointHoverRadius: 6,
                    },
                    {
                        label: 'Upper Bound',
                        data: @json($dailyUpperBound),
                        borderColor: '#9aaa8a', borderWidth: 1.5, borderDash: [5, 5],
                        fill: false, tension: 0.4, pointRadius: 0,
                    },
                    {
                        label: 'Lower Bound',
                        data: @json($dailyLowerBound),
                        borderColor: '#9aaa8a', backgroundColor: 'rgba(154, 170, 138, 0.1)',
                        borderWidth: 1.5, borderDash: [5, 5], fill: '-1', tension: 0.4, pointRadius: 0,
                    }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: true, position: 'top' },
                    tooltip: { callbacks: { label: ctx => ctx.dataset.label + ': ' + ctx.parsed.y.toFixed(1) } }
                },
                scales: {
                    y: { beginAtZero: true, title: { display: true, text: 'Visitors' } },
                    x: { title: { display: true, text: 'Date' } }
                }
            }
        });
    }

    @if($weeklyData)
    function buildWeeklyChart() {
        const ctx = document.getElementById('weeklySummaryChart');
        if (!ctx) return;

        if (window.weeklyChart && typeof window.weeklyChart.destroy === 'function') window.weeklyChart.destroy();

        window.weeklyChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: @json($weeklyData['labels']),
                datasets: [{ label: 'Total Visitors', data: @json($weeklyData['totals']), backgroundColor: '#4A2F24', borderRadius: 8 }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, title: { display: true, text: 'Total Visitors' } } }
            }
        });
    }
    @endif
</script>
@endpush
@endif
