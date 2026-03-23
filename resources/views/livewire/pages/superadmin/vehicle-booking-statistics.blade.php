<div class="min-h-screen bg-gray-50">
    <main class="max-w-7xl mx-auto px-4 sm:px-6 py-8 space-y-8">
        {{-- HEADER --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Vehicle Booking Statistics</h1>
                <p class="text-sm text-gray-500">Monitor vehicle booking activity</p>
            </div>
        </div>

        {{-- KPIs --}}
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($kpis as $kpi)
                @php
                    $colors = [
                        'blue' => 'text-blue-600',
                        'yellow' => 'text-yellow-600',
                        'green' => 'text-green-600',
                        'purple' => 'text-purple-600',
                    ];
                @endphp
                <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm hover:shadow-lg hover:-translate-y-1 transition">
                    <p class="text-sm font-medium text-gray-500">{{ $kpi['label'] }}</p>
                    <h2 class="text-3xl font-bold mt-2 {{ $colors[$kpi['color']] }}">{{ $kpi['value'] }}</h2>
                </div>
            @endforeach
        </section>

        {{-- CHART --}}
        <div class="bg-white border border-gray-200 p-6 rounded-2xl shadow-sm">
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Monthly Booking Trend</h3>
                <div class="flex gap-2">
                    <button wire:click="setChartType('line')" 
                        class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $chartType === 'line' ? 'bg-blue-600 text-white' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50' }}">
                        Line
                    </button>
                    <button wire:click="setChartType('bar')" 
                        class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $chartType === 'bar' ? 'bg-blue-600 text-white' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50' }}">
                        Bar
                    </button>
                    <button wire:click="toggleList" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium transition">
                        {{ $showList ? 'Hide List' : 'Show List' }}
                    </button>
                </div>
            </div>
            <canvas id="vehicleChart"></canvas>
        </div>

        {{-- BOOKING LIST --}}
        @if($showList)
            <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b bg-gray-50">
                    <h3 class="font-semibold text-gray-900">Vehicle Booking Items</h3>
                </div>
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500 uppercase text-xs border-b">
                        <tr>
                            <th class="px-6 py-3 text-left font-medium">ID</th>
                            <th class="px-6 py-3 text-left font-medium">Vehicle</th>
                            <th class="px-6 py-3 text-left font-medium">Borrower</th>
                            <th class="px-6 py-3 text-left font-medium">Purpose</th>
                            <th class="px-6 py-3 text-left font-medium">Destination</th>
                            <th class="px-6 py-3 text-left font-medium">Start</th>
                            <th class="px-6 py-3 text-left font-medium">End</th>
                            <th class="px-6 py-3 text-left font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($bookings as $booking)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-gray-900">{{ $booking->vehiclebooking_id }}</td>
                                <td class="px-6 py-4 text-gray-900">{{ $booking->vehicle->vehicle_name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 text-gray-900">{{ $booking->borrower_name }}</td>
                                <td class="px-6 py-4 text-gray-900">{{ Str::limit($booking->purpose, 30) }}</td>
                                <td class="px-6 py-4 text-gray-900">{{ $booking->destination }}</td>
                                <td class="px-6 py-4 text-gray-900">{{ $booking->start_at->format('M d, H:i') }}</td>
                                <td class="px-6 py-4 text-gray-900">{{ $booking->end_at->format('M d, H:i') }}</td>
                                <td class="px-6 py-4">
                                    @php
                                        $statusColors = [
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'approved' => 'bg-green-100 text-green-800',
                                            'rejected' => 'bg-red-100 text-red-800',
                                            'completed' => 'bg-purple-100 text-purple-800',
                                        ];
                                        $statusValue = strtolower($booking->status);
                                    @endphp
                                    <span class="px-3 py-1 text-xs rounded-full font-medium {{ $statusColors[$statusValue] ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ ucfirst($statusValue) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-8 text-center text-gray-500">No bookings found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif
    </main>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @script
    <script>
        function buildVehicleChart() {
            const ctx = document.getElementById('vehicleChart')?.getContext('2d');
            if (!ctx) return;

            const labels = @json($labels);
            const data = @json($data);
            const chartType = @json($chartType);

            if (window.vehicleChart) window.vehicleChart.destroy();

            window.vehicleChart = new Chart(ctx, {
                type: chartType,
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Vehicle Bookings',
                        data: data,
                        borderColor: '#059669',
                        backgroundColor: chartType === 'bar' ? '#059669' : 'rgba(5, 150, 105, 0.1)',
                        fill: chartType === 'line',
                        tension: 0.4,
                        borderRadius: chartType === 'bar' ? 8 : 0,
                    }]
                },
                options: {
                    responsive: true,
                    animation: { duration: 600 },
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        }

        document.addEventListener('DOMContentLoaded', buildVehicleChart);
        document.addEventListener('livewire:updated', buildVehicleChart);
    </script>
    @endscript
</div>
