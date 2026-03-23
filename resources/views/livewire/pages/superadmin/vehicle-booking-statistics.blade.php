<div class="min-h-screen bg-gray-50">
    <main class="px-4 sm:px-6 py-6 space-y-8">
        {{-- HEADER --}}
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-semibold">Vehicle Booking Statistics</h1>
                <p class="text-sm text-gray-500">Monitor vehicle booking activity</p>
            </div>
            <div class="flex gap-2">
                <button wire:click="setChartType('line')" 
                    class="px-4 py-2 rounded-lg text-sm transition {{ $chartType === 'line' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700' }}">
                    Line
                </button>
                <button wire:click="setChartType('bar')" 
                    class="px-4 py-2 rounded-lg text-sm transition {{ $chartType === 'bar' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700' }}">
                    Bar
                </button>
            </div>
        </div>

        {{-- KPIs --}}
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($kpis as $kpi)
                @php
                    $colors = [
                        'blue' => 'bg-blue-50 text-blue-600',
                        'yellow' => 'bg-yellow-50 text-yellow-600',
                        'green' => 'bg-green-50 text-green-600',
                        'purple' => 'bg-purple-50 text-purple-600',
                    ];
                @endphp
                <div class="bg-white rounded-2xl p-5 shadow-sm hover:shadow-lg hover:-translate-y-1 transition">
                    <p class="text-sm text-gray-500">{{ $kpi['label'] }}</p>
                    <h2 class="text-3xl font-bold mt-2 {{ $colors[$kpi['color']] }}">{{ $kpi['value'] }}</h2>
                </div>
            @endforeach
        </section>

        {{-- CHART --}}
        <div class="bg-white p-6 rounded-2xl shadow-sm">
            <h3 class="text-lg font-semibold mb-4">Monthly Booking Trend</h3>
            <canvas id="vehicleChart"></canvas>
        </div>

        {{-- BOOKING LIST --}}
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <button wire:click="toggleList" 
                class="w-full px-6 py-4 flex justify-between items-center hover:bg-gray-50 transition">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" />
                    </svg>
                    <span class="font-semibold text-gray-700">Vehicle Booking Items</span>
                </div>
                <svg class="w-5 h-5 transition-transform {{ $showList ? 'rotate-180' : '' }}" 
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            @if($showList)
                <div class="border-t overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vehicle</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Borrower</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Purpose</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Destination</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Start</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">End</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($bookings as $booking)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $booking->vehiclebooking_id }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $booking->vehicle->vehicle_name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $booking->borrower_name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ Str::limit($booking->purpose, 30) }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $booking->destination }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $booking->start_at->format('M d, H:i') }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $booking->end_at->format('M d, H:i') }}</td>
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
                                        <span class="px-2 py-1 text-xs rounded-full {{ $statusColors[$statusValue] ?? 'bg-gray-100 text-gray-800' }}">
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
        </div>
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
