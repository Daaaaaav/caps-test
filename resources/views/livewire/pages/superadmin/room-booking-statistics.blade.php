<div class="min-h-screen bg-gray-50">
    <main class="px-4 sm:px-6 py-6 space-y-8">
        {{-- HEADER --}}
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-semibold">Room Booking Statistics</h1>
                <p class="text-sm text-gray-500">Analyze room booking patterns</p>
            </div>
            <div class="flex gap-2">
                <button wire:click="setViewType('daily')" 
                    class="px-4 py-2 rounded-lg text-sm transition {{ $viewType === 'daily' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700' }}">
                    Daily
                </button>
                <button wire:click="setViewType('monthly')" 
                    class="px-4 py-2 rounded-lg text-sm transition {{ $viewType === 'monthly' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700' }}">
                    Monthly
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
                        'red' => 'bg-red-50 text-red-600',
                    ];
                @endphp
                <div class="bg-white rounded-2xl p-5 shadow-sm hover:shadow-lg transition">
                    <p class="text-sm text-gray-500">{{ $kpi['label'] }}</p>
                    <h2 class="text-3xl font-bold mt-2 {{ $colors[$kpi['color']] }}">{{ $kpi['value'] }}</h2>
                </div>
            @endforeach
        </section>

        {{-- CHART --}}
        <div class="bg-white p-6 rounded-2xl shadow-sm">
            <h3 class="text-lg font-semibold mb-4">Booking Trend ({{ ucfirst($viewType) }})</h3>
            <canvas id="roomChart"></canvas>
        </div>

        {{-- BOOKING LIST --}}
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <button wire:click="toggleList" 
                class="w-full px-6 py-4 flex justify-between items-center hover:bg-gray-50 transition">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <span class="text-gray-700 font-semibold">Room Booking Items</span>
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
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Room</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Meeting Title</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($bookings as $booking)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $booking->bookingroom_id }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $booking->room->room_name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $booking->user->name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $booking->meeting_title }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $booking->date->format('M d, Y') }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $booking->start_time }} - {{ $booking->end_time }}</td>
                                    <td class="px-6 py-4">
                                        @php
                                            $statusColors = [
                                                'pending' => 'bg-yellow-100 text-yellow-800',
                                                'approved' => 'bg-green-100 text-green-800',
                                                'rejected' => 'bg-red-100 text-red-800',
                                                'done' => 'bg-blue-100 text-blue-800',
                                            ];
                                            $statusValue = is_numeric($booking->status) 
                                                ? ['pending', 'approved', 'rejected', 'done'][$booking->status] ?? 'pending'
                                                : strtolower($booking->status);
                                        @endphp
                                        <span class="px-2 py-1 text-xs rounded-full {{ $statusColors[$statusValue] ?? 'bg-gray-100 text-gray-800' }}">
                                            {{ ucfirst($statusValue) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-8 text-center text-gray-500">No bookings found</td>
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
        function buildRoomChart() {
            const ctx = document.getElementById('roomChart')?.getContext('2d');
            if (!ctx) return;

            const labels = @json($labels);
            const data = @json($data);

            if (window.roomChart) window.roomChart.destroy();

            window.roomChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Room Bookings',
                        data: data,
                        borderColor: '#2563eb',
                        backgroundColor: 'rgba(37, 99, 235, 0.1)',
                        fill: true,
                        tension: 0.4,
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

        document.addEventListener('DOMContentLoaded', buildRoomChart);
        document.addEventListener('livewire:updated', buildRoomChart);
    </script>
    @endscript
</div>
