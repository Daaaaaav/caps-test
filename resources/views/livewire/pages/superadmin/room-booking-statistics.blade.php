<div class="min-h-screen bg-gray-50">
    <main class="max-w-7xl mx-auto px-4 sm:px-6 py-8 space-y-8">
        {{-- HEADER --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Room Booking Statistics</h1>
                <p class="text-sm text-gray-500">Analyze room booking patterns &mdash; {{ $selectedYear }}</p>
            </div>
        </div>

        {{-- YEAR SELECTOR --}}
        <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-sm font-medium text-gray-500">Select Year</p>
                    <p class="text-xs text-gray-400">KPIs and monthly chart update per year</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    @foreach($availableYears as $year)
                        <button wire:click="setYear({{ $year }})"
                            class="px-4 py-2 rounded-lg text-sm font-medium transition
                                {{ $selectedYear === $year ? 'bg-gray-900 text-white shadow-md' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            {{ $year }}
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- KPIs --}}
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            @foreach($kpis as $kpi)
                @php
                    $colors = [
                        'blue'   => 'text-blue-600',
                        'yellow' => 'text-yellow-600',
                        'green'  => 'text-green-600',
                        'red'    => 'text-red-600',
                        'gray'   => 'text-gray-600',
                    ];
                @endphp
                <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm hover:shadow-lg transition">
                    <p class="text-sm font-medium text-gray-500">{{ $kpi['label'] }}</p>
                    <h2 class="text-3xl font-bold mt-2 {{ $colors[$kpi['color']] ?? 'text-gray-900' }}">{{ $kpi['value'] }}</h2>
                </div>
            @endforeach
        </section>

        {{-- CHART --}}
        <div class="bg-white border border-gray-200 p-6 rounded-2xl shadow-sm">
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Booking Trend — {{ $selectedYear }} ({{ ucfirst($viewType) }})</h3>
                <div class="flex gap-2">
                    <button wire:click="setViewType('daily')"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $viewType === 'daily' ? 'bg-gray-900 text-white' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50' }}">
                        Daily
                    </button>
                    <button wire:click="setViewType('monthly')"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $viewType === 'monthly' ? 'bg-gray-900 text-white' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50' }}">
                        Monthly
                    </button>
                    <button wire:click="toggleList"
                        class="px-4 py-2 bg-gray-900 text-white rounded-lg hover:bg-gray-800 text-sm font-medium transition">
                        {{ $showList ? 'Hide List' : 'Show List' }}
                    </button>
                </div>
            </div>
            <div wire:ignore style="position: relative; height: 400px;">
                <canvas id="roomChart"></canvas>
            </div>
        </div>

        {{-- BOOKING LIST --}}
        @if($showList)
            <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b bg-gray-50">
                    <h3 class="font-semibold text-gray-900">Room Booking Items</h3>
                </div>
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500 uppercase text-xs border-b">
                        <tr>
                            <th class="px-6 py-3 text-left font-medium">ID</th>
                            <th class="px-6 py-3 text-left font-medium">Room</th>
                            <th class="px-6 py-3 text-left font-medium">User</th>
                            <th class="px-6 py-3 text-left font-medium">Meeting Title</th>
                            <th class="px-6 py-3 text-left font-medium">Date</th>
                            <th class="px-6 py-3 text-left font-medium">Time</th>
                            <th class="px-6 py-3 text-left font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($bookings as $booking)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-gray-900">{{ $booking->bookingroom_id }}</td>
                                <td class="px-6 py-4 text-gray-900">{{ $booking->room->room_name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 text-gray-900">{{ $booking->user->name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 text-gray-900">{{ $booking->meeting_title }}</td>
                                <td class="px-6 py-4 text-gray-900">{{ $booking->date->format('M d, Y') }}</td>
                                <td class="px-6 py-4 text-gray-900">{{ $booking->start_time }} - {{ $booking->end_time }}</td>
                                <td class="px-6 py-4">
                                    @php
                                        $statusValue = is_numeric($booking->status)
                                            ? (['pending', 'approved', 'rejected', 'done'][$booking->status] ?? 'pending')
                                            : strtolower($booking->status);
                                        $statusColors = [
                                            'pending'   => 'bg-yellow-100 text-yellow-800',
                                            'approved'  => 'bg-green-100 text-green-800',
                                            'rejected'  => 'bg-red-100 text-red-800',
                                            'completed' => 'bg-gray-100 text-gray-700',
                                            'done'      => 'bg-gray-100 text-gray-700',
                                        ];
                                    @endphp
                                    <span class="px-3 py-1 text-xs rounded-full font-medium {{ $statusColors[$statusValue] ?? 'bg-gray-100 text-gray-700' }}">
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
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    function buildRoomChart(labels, data) {
        const ctx = document.getElementById('roomChart');
        if (!ctx) return;

        if (window.roomChart && typeof window.roomChart.destroy === 'function') {
            window.roomChart.destroy();
        }

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
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    borderWidth: 2.5,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: { duration: 400 },
                interaction: { mode: 'index', intersect: false },
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 }, title: { display: true, text: 'Bookings' } },
                    x: { title: { display: true, text: 'Period' } }
                }
            }
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        buildRoomChart(@json($labels), @json($data));
    });

    document.addEventListener('livewire:init', () => {
        Livewire.on('room-chart-updated', ({ labels, data }) => {
            buildRoomChart(labels, data);
        });
    });
</script>
