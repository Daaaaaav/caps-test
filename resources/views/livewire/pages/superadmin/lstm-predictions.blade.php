<div class="min-h-screen bg-gray-50">
    <main class="max-w-7xl mx-auto px-4 sm:px-6 py-8 space-y-8">
        {{-- HEADER --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">
                    {{ $title ?? 'LSTM Model Predictions' }}
                </h1>
                @if($description)
                    <p class="text-sm text-gray-500 mt-1">{{ $description }}</p>
                @else
                    <p class="text-sm text-gray-500 mt-1">Advanced AI predictions using deep learning</p>
                @endif
            </div>
            
            @if($isLSTMAvailable)
                <div class="flex items-center gap-2">
                    <span class="flex items-center gap-2 px-3 py-1.5 bg-green-100 text-green-700 rounded-lg text-sm font-medium">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        LSTM Active
                    </span>
                </div>
            @else
                <div class="flex items-center gap-2">
                    <span class="flex items-center gap-2 px-3 py-1.5 bg-red-100 text-red-700 rounded-lg text-sm font-medium">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        LSTM Offline
                    </span>
                </div>
            @endif
        </div>

        @if(!$isLSTMAvailable)
            {{-- SERVICE UNAVAILABLE MESSAGE --}}
            <div class="bg-yellow-50 border border-yellow-200 rounded-2xl p-6">
                <div class="flex items-start gap-4">
                    <svg class="w-6 h-6 text-yellow-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-yellow-900">LSTM Service Not Available</h3>
                        <p class="text-yellow-700 mt-1">The LSTM prediction service is currently offline. Please start the service to view predictions.</p>
                        <div class="mt-4 flex gap-3">
                            <button onclick="window.location.reload()" class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 text-sm font-medium">
                                Retry Connection
                            </button>
                            <a href="#" class="px-4 py-2 bg-white border border-yellow-300 text-yellow-700 rounded-lg hover:bg-yellow-50 text-sm font-medium">
                                View Documentation
                            </a>
                        </div>
                        <div class="mt-4 p-4 bg-white rounded-lg border border-yellow-200">
                            <p class="text-sm font-medium text-gray-900 mb-2">To start the LSTM service:</p>
                            <code class="block bg-gray-900 text-green-400 p-3 rounded text-sm">
                                start_lstm_service.bat
                            </code>
                        </div>
                    </div>
                </div>
            </div>
        @else
            {{-- CONTROLS --}}
            <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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

                    {{-- Prediction Type --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Prediction Type</label>
                        <select wire:model.live="predictionType" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-gray-500">
                            <option value="room_booking">Room Bookings</option>
                            <option value="vehicle_booking">Vehicle Bookings</option>
                            <option value="guestbook">Visitor Traffic</option>
                            <option value="delivery">Deliveries</option>
                        </select>
                    </div>

                    {{-- Data Source --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Data Source</label>
                        <button wire:click="toggleDummyData" 
                            class="w-full px-4 py-2 rounded-lg text-sm font-medium transition {{ $useDummyData ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            {{ $useDummyData ? 'Dummy Data' : 'Real Data' }}
                        </button>
                    </div>
                </div>
            </div>

            @if($predictions)
                {{-- STATS CARDS --}}
                <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                                    @foreach($stats as $stat)
                        @php
                            $colors = [
                                'blue' => 'text-gray-700 bg-gray-100',
                                'green' => 'text-gray-700 bg-gray-100',
                                'yellow' => 'text-gray-700 bg-gray-100',
                                'purple' => 'text-gray-700 bg-gray-100',
                            ];
                        @endphp
                        <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm hover:shadow-lg transition">
                            <div class="flex items-center justify-between mb-2">
                                <p class="text-sm font-medium text-gray-500">{{ $stat['label'] }}</p>
                                <div class="w-10 h-10 rounded-lg {{ $colors[$stat['color']] }} flex items-center justify-center">
                                    <x-dynamic-component :component="'heroicon-o-' . $stat['icon']" class="w-5 h-5" />
                                </div>
                            </div>
                            <h2 class="text-3xl font-bold text-gray-900">{{ $stat['value'] }}</h2>
                        </div>
                    @endforeach
                </section>

                {{-- DAILY PREDICTIONS CHART --}}
                <div class="bg-white border border-gray-200 p-6 rounded-2xl shadow-sm">
                    <div class="mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Daily Predictions with Confidence Intervals</h3>
                        <p class="text-sm text-gray-500 mt-1">
                            RMSE: {{ number_format($rmse, 4) }} | 
                            Data Source: <span class="font-medium">{{ ucfirst($dataSource) }}</span>
                        </p>
                    </div>
                    <div wire:ignore style="position: relative; height: 400px;">
                        <canvas id="dailyPredictionsChart"></canvas>
                    </div>
                </div>

                {{-- WEEKLY SUMMARY CHART (if 21 days) --}}
                @if($weeklyData)
                    <div class="bg-white border border-gray-200 p-6 rounded-2xl shadow-sm">
                        <div class="mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Weekly Summary</h3>
                            <p class="text-sm text-gray-500 mt-1">Total bookings grouped by week</p>
                        </div>
                        <div wire:ignore style="position: relative; height: 300px;">
                            <canvas id="weeklySummaryChart"></canvas>
                        </div>
                    </div>
                @endif

                {{-- PREDICTIONS TABLE --}}
                <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b bg-gray-50">
                        <h3 class="font-semibold text-gray-900">Detailed Predictions</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 text-gray-500 uppercase text-xs border-b">
                                <tr>
                                    <th class="px-6 py-3 text-left font-medium">Date</th>
                                    <th class="px-6 py-3 text-left font-medium">Day</th>
                                    <th class="px-6 py-3 text-right font-medium">Predicted</th>
                                    <th class="px-6 py-3 text-right font-medium">Lower Bound</th>
                                    <th class="px-6 py-3 text-right font-medium">Upper Bound</th>
                                    <th class="px-6 py-3 text-right font-medium">Confidence</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($predictions as $pred)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 text-gray-900 font-medium">{{ $pred['date'] }}</td>
                                        <td class="px-6 py-4 text-gray-600">{{ date('l', strtotime($pred['date'])) }}</td>
                                        <td class="px-6 py-4 text-right text-gray-900 font-semibold">{{ number_format($pred['predicted'], 1) }}</td>
                                        <td class="px-6 py-4 text-right text-gray-600">{{ number_format($pred['lower_bound'], 1) }}</td>
                                        <td class="px-6 py-4 text-right text-gray-600">{{ number_format($pred['upper_bound'], 1) }}</td>
                                        <td class="px-6 py-4 text-right">
                                            <span class="px-2 py-1 text-xs rounded-full font-medium 
                                                {{ $pred['confidence'] >= 0.8 ? 'bg-green-100 text-green-700' : ($pred['confidence'] >= 0.6 ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                                                {{ number_format($pred['confidence'] * 100, 1) }}%
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        @endif
    </main>
</div>

@if($isLSTMAvailable && $predictions)
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        initCharts();
    });

    // Listen for Livewire updates
    document.addEventListener('livewire:init', () => {
        Livewire.hook('morph.updated', ({ el, component }) => {
            // Re-initialize charts after Livewire updates
            setTimeout(() => initCharts(), 150);
        });
    });

    function initCharts() {
        buildDailyChart();
        @if($weeklyData)
        buildWeeklyChart();
        @endif
    }

    function buildDailyChart() {
        const ctx = document.getElementById('dailyPredictionsChart');
        if (!ctx) return;

        const labels = @json($dailyLabels);
        const predicted = @json($dailyPredicted);
        const lowerBound = @json($dailyLowerBound);
        const upperBound = @json($dailyUpperBound);

        // Destroy existing chart
        if (window.dailyChart && typeof window.dailyChart.destroy === 'function') {
            window.dailyChart.destroy();
        }

        window.dailyChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Predicted',
                        data: predicted,
                        borderColor: '#1f2937',
                        backgroundColor: 'rgba(31, 41, 55, 0.1)',
                        borderWidth: 3,
                        fill: false,
                        tension: 0.4,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                    },
                    {
                        label: 'Upper Bound',
                        data: upperBound,
                        borderColor: '#9ca3af',
                        borderWidth: 2,
                        borderDash: [5, 5],
                        fill: false,
                        tension: 0.4,
                        pointRadius: 0,
                    },
                    {
                        label: 'Lower Bound',
                        data: lowerBound,
                        borderColor: '#9ca3af',
                        backgroundColor: 'rgba(156, 163, 175, 0.1)',
                        borderWidth: 2,
                        borderDash: [5, 5],
                        fill: '-1',
                        tension: 0.4,
                        pointRadius: 0,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + context.parsed.y.toFixed(1);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Bookings'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Date'
                        }
                    }
                }
            }
        });
    }

    @if($weeklyData)
    function buildWeeklyChart() {
        const ctx = document.getElementById('weeklySummaryChart');
        if (!ctx) return;

        const labels = @json($weeklyData['labels']);
        const totals = @json($weeklyData['totals']);

        // Destroy existing chart
        if (window.weeklyChart && typeof window.weeklyChart.destroy === 'function') {
            window.weeklyChart.destroy();
        }

        window.weeklyChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Total Bookings',
                    data: totals,
                    backgroundColor: '#4b5563',
                    borderRadius: 8,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Total Bookings'
                        }
                    }
                }
            }
        });
    }
    @endif
</script>
@endpush
@endif
