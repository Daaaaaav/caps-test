<div class="min-h-screen bg-gray-50">
    <main class="max-w-7xl mx-auto px-4 sm:px-6 py-8 space-y-8">

        {{-- HEADER --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Occupancy Forecasting</h1>
                <p class="text-sm text-gray-500 mt-1">AI-powered booking occupancy predictions</p>
            </div>
            <span class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm font-medium
                {{ $isLSTMAvailable ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                {{ $isLSTMAvailable ? 'LSTM Neural Network' : 'Statistical Model' }}
            </span>
        </div>

        {{-- CONTROLS --}}
        <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                {{-- Forecast Type --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Forecast Type</label>
                    <div class="flex gap-2">
                        <button wire:click="setForecastType('room')"
                            class="flex-1 px-4 py-2 rounded-lg text-sm font-medium transition {{ $forecastType === 'room' ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            Rooms
                        </button>
                        <button wire:click="setForecastType('vehicle')"
                            class="flex-1 px-4 py-2 rounded-lg text-sm font-medium transition {{ $forecastType === 'vehicle' ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            Vehicles
                        </button>
                        <button wire:click="setForecastType('combined')"
                            class="flex-1 px-4 py-2 rounded-lg text-sm font-medium transition {{ $forecastType === 'combined' ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            Both
                        </button>
                    </div>
                </div>

                {{-- Forecast Period --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Forecast Period</label>
                    <div class="flex gap-2">
                        <button wire:click="setForecastDays(7)"
                            class="flex-1 px-4 py-2 rounded-lg text-sm font-medium transition {{ $forecastDays === 7 ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            7 Days
                        </button>
                        <button wire:click="setForecastDays(14)"
                            class="flex-1 px-4 py-2 rounded-lg text-sm font-medium transition {{ $forecastDays === 14 ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            14 Days
                        </button>
                        <button wire:click="setForecastDays(21)"
                            class="flex-1 px-4 py-2 rounded-lg text-sm font-medium transition {{ $forecastDays === 21 ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            21 Days
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- STATS CARDS --}}
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm hover:shadow-lg transition">
                <p class="text-sm font-medium text-gray-500 mb-2">Avg Room Occupancy</p>
                <h2 class="text-3xl font-bold text-gray-900">{{ $stats['avg_room_fc'] }}</h2>
                <p class="text-xs text-gray-400 mt-1">
                    Historical: {{ $stats['avg_room_hist'] }}
                    @if($stats['room_trend'] != 0)
                        <span class="{{ $stats['room_trend'] > 0 ? 'text-green-600' : 'text-red-600' }}">
                            ({{ $stats['room_trend'] > 0 ? '+' : '' }}{{ $stats['room_trend'] }}%)
                        </span>
                    @endif
                </p>
            </div>

            <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm hover:shadow-lg transition">
                <p class="text-sm font-medium text-gray-500 mb-2">Avg Vehicle Bookings</p>
                <h2 class="text-3xl font-bold text-gray-900">{{ $stats['avg_vehicle_fc'] }}</h2>
                <p class="text-xs text-gray-400 mt-1">
                    Historical: {{ $stats['avg_vehicle_hist'] }}
                    @if($stats['vehicle_trend'] != 0)
                        <span class="{{ $stats['vehicle_trend'] > 0 ? 'text-green-600' : 'text-red-600' }}">
                            ({{ $stats['vehicle_trend'] > 0 ? '+' : '' }}{{ $stats['vehicle_trend'] }}%)
                        </span>
                    @endif
                </p>
            </div>

            <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm hover:shadow-lg transition">
                <p class="text-sm font-medium text-gray-500 mb-2">Peak Day</p>
                <h2 class="text-2xl font-bold text-gray-900">{{ $stats['peak_day'] }}</h2>
                <p class="text-xs text-gray-400 mt-1">Highest predicted occupancy</p>
            </div>

            <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm hover:shadow-lg transition">
                <p class="text-sm font-medium text-gray-500 mb-2">Total Forecast</p>
                <h2 class="text-3xl font-bold text-gray-900">{{ $stats['total_room_fc'] }}</h2>
                <p class="text-xs text-gray-400 mt-1">Room bookings ({{ $forecastDays }} days)</p>
            </div>
        </section>

        {{-- WEATHER INSIGHTS (only shown when weather data is available) --}}
        @if(!empty($weatherInsight))
            <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Weather Impact Insights
                </h3>
                <div class="space-y-3">
                    @foreach($weatherInsight as $insight)
                        <div class="flex items-start gap-3 p-4 rounded-lg {{ $insight['type'] === 'warning' ? 'bg-yellow-50 border border-yellow-200' : 'bg-green-50 border border-green-200' }}">
                            <span class="text-2xl">{{ $insight['icon'] }}</span>
                            <p class="text-sm {{ $insight['type'] === 'warning' ? 'text-yellow-800' : 'text-green-800' }} flex-1">
                                {{ $insight['message'] }}
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- FORECAST CHART --}}
        @if($roomForecast || $vehicleForecast)
            <div class="bg-white border border-gray-200 p-6 rounded-2xl shadow-sm">
                <div class="mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Occupancy Forecast</h3>
                    <p class="text-sm text-gray-500 mt-1">
                        {{ $forecastDays }}-day prediction based on historical patterns
                        ({{ $isLSTMAvailable ? 'LSTM neural network' : 'statistical model' }})
                    </p>
                </div>
                <div wire:ignore style="position: relative; height: 400px;">
                    <canvas id="occupancyChart"></canvas>
                </div>
            </div>
        @endif

        {{-- WEATHER MINI CARDS (only shown when weather data is available) --}}
        @if(!empty($weather['forecast']))
            <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">3-Day Weather Outlook</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    @foreach($weather['forecast'] as $day)
                        <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-lg">
                            <span class="text-4xl">{{ $day['weather_icon'] }}</span>
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-gray-900">{{ $day['date_label'] }}</p>
                                <p class="text-xs text-gray-500">{{ $day['summary']['weather_desc'] ?? '—' }}</p>
                                <p class="text-lg font-bold text-gray-900 mt-1">{{ $day['max_temp'] }}°C</p>
                                <p class="text-xs text-gray-400">🌧️ {{ $day['rain_chance'] }}% rain</p>
                            </div>
                        </div>
                    @endforeach
                </div>
                <p class="text-xs text-gray-400 mt-4 text-center">
                    Data from <a href="https://data.bmkg.go.id" target="_blank" class="underline">BMKG</a>
                </p>
            </div>
        @endif

    </main>
</div>

@if($roomForecast || $vehicleForecast)
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() { initChart(); });

    document.addEventListener('livewire:init', () => {
        Livewire.hook('morph.updated', ({ el, component }) => {
            setTimeout(() => initChart(), 150);
        });
    });

    function initChart() {
        const ctx = document.getElementById('occupancyChart');
        if (!ctx) return;

        if (window.occupancyChart && typeof window.occupancyChart.destroy === 'function') {
            window.occupancyChart.destroy();
        }

        const datasets = [];
        const roomData = @json($chartData['roomData']);
        const vehicleData = @json($chartData['vehicleData']);

        if (roomData && roomData.some(v => v !== null)) {
            datasets.push({
                label: 'Room Bookings',
                data: roomData,
                borderColor: '#1f2937',
                backgroundColor: 'rgba(31, 41, 55, 0.1)',
                borderWidth: 3,
                tension: 0.4,
                pointRadius: 4,
                pointHoverRadius: 6,
            });
        }

        if (vehicleData && vehicleData.length > 0) {
            datasets.push({
                label: 'Vehicle Bookings',
                data: vehicleData,
                borderColor: '#6b7280',
                backgroundColor: 'rgba(107, 114, 128, 0.1)',
                borderWidth: 3,
                tension: 0.4,
                pointRadius: 4,
                pointHoverRadius: 6,
            });
        }

        window.occupancyChart = new Chart(ctx, {
            type: 'line',
            data: { labels: @json($chartData['labels']), datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: true, position: 'top' },
                    tooltip: {
                        callbacks: {
                            label: ctx => ctx.dataset.label + ': ' + ctx.parsed.y.toFixed(1)
                        }
                    }
                },
                scales: {
                    y: { beginAtZero: true, title: { display: true, text: 'Bookings' } },
                    x: { title: { display: true, text: 'Date' } }
                }
            }
        });
    }
</script>
@endpush
@endif
