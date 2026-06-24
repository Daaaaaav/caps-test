<div class="min-h-screen bg-[#f5f7f2]">
    <main class="max-w-7xl mx-auto px-4 sm:px-6 py-8 space-y-8">

        {{-- HEADER --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-[#2d3a24]">{{ __('app.room_booking_stats_title') }}</h1>
                <p class="text-sm text-[#7a8f6a]">{{ __('app.room_booking_stats_sub') }}</p>
            </div>
            <div class="flex gap-2">
                <button wire:click="setTimeRange('7days')"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $timeRange === '7days' ? 'bg-[#4A2F24] text-white' : 'bg-white border border-[#d4dfc8] text-[#4E653D] hover:bg-[#f0f4eb]' }}">
                    {{ __('app.7_days') }}
                </button>
                <button wire:click="setTimeRange('30days')"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $timeRange === '30days' ? 'bg-[#4A2F24] text-white' : 'bg-white border border-[#d4dfc8] text-[#4E653D] hover:bg-[#f0f4eb]' }}">
                    {{ __('app.30_days') }}
                </button>
                <button wire:click="setTimeRange('90days')"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $timeRange === '90days' ? 'bg-[#4A2F24] text-white' : 'bg-white border border-[#d4dfc8] text-[#4E653D] hover:bg-[#f0f4eb]' }}">
                    {{ __('app.90_days') }}
                </button>
            </div>
        </div>

        {{-- KPIs --}}
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            @foreach($kpis as $kpi)
                @php
                    $colors = [
                        'blue'   => 'text-[#4E653D]',
                        'yellow' => 'text-yellow-600',
                        'green'  => 'text-green-600',
                        'red'    => 'text-red-600',
                        'gray'   => 'text-[#5a6e4a]',
                    ];
                @endphp
                <div class="bg-white border border-[#d4dfc8] rounded-2xl p-5 shadow-sm hover:shadow-lg transition">
                    <p class="text-sm font-medium text-[#7a8f6a]">{{ $kpi['label'] }}</p>
                    <h2 class="text-3xl font-bold mt-2 {{ $colors[$kpi['color']] ?? 'text-[#2d3a24]' }}">{{ $kpi['value'] }}</h2>
                </div>
            @endforeach
        </section>

        {{-- CHART --}}
        <div class="bg-white border border-[#d4dfc8] p-6 rounded-2xl shadow-sm">
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-4">
                <h3 class="text-lg font-semibold text-[#2d3a24]">{{ __('app.daily_booking_trend') }}</h3>
                <button wire:click="toggleList"
                    class="px-4 py-2 bg-[#4A2F24] text-white rounded-lg hover:bg-[#3d2720] text-sm font-medium transition">
                    {{ $showList ? __('app.hide_list') : __('app.show_list') }}
                </button>
            </div>
            <div wire:ignore style="position: relative; height: 400px;">
                <canvas id="roomChart"></canvas>
            </div>
        </div>

        {{-- BOOKING LIST --}}
        @if($showList)
            <div class="bg-white border border-[#d4dfc8] rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b bg-[#f0f4eb]">
                    <h3 class="font-semibold text-[#2d3a24]">{{ __('app.room_booking_items') }}</h3>
                </div>
                <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[700px]">
                    <thead class="bg-[#f0f4eb] text-[#7a8f6a] uppercase text-xs border-b">
                        <tr>
                            <th class="px-6 py-3 text-left font-medium">ID</th>
                            <th class="px-6 py-3 text-left font-medium">{{ __('app.room') }}</th>
                            <th class="px-6 py-3 text-left font-medium">{{ __('app.name') }}</th>
                            <th class="px-6 py-3 text-left font-medium">{{ __('app.meeting_title_col') }}</th>
                            <th class="px-6 py-3 text-left font-medium">{{ __('app.date') }}</th>
                            <th class="px-6 py-3 text-left font-medium">{{ __('app.time') }}</th>
                            <th class="px-6 py-3 text-left font-medium">{{ __('app.status') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#d4dfc8]">
                        @forelse($bookings as $booking)
                            <tr class="hover:bg-[#f0f4eb]">
                                <td class="px-6 py-4 text-[#2d3a24]">{{ $booking->bookingroom_id }}</td>
                                <td class="px-6 py-4 text-[#2d3a24]">{{ $booking->room->room_name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 text-[#2d3a24]">{{ $booking->user->name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 text-[#2d3a24]">{{ $booking->meeting_title }}</td>
                                <td class="px-6 py-4 text-[#2d3a24]">{{ $booking->date->format('M d, Y') }}</td>
                                <td class="px-6 py-4 text-[#2d3a24]">{{ $booking->start_time }} - {{ $booking->end_time }}</td>
                                <td class="px-6 py-4">
                                    @php
                                        $statusValue = is_numeric($booking->status)
                                            ? (['pending', 'approved', 'rejected', 'done'][$booking->status] ?? 'pending')
                                            : strtolower($booking->status);
                                        $statusColors = [
                                            'pending'   => 'bg-yellow-100 text-yellow-800',
                                            'approved'  => 'bg-green-100 text-green-800',
                                            'rejected'  => 'bg-red-100 text-red-800',
                                            'completed' => 'bg-[#eef1e8] text-[#4E653D]',
                                            'done'      => 'bg-[#eef1e8] text-[#4E653D]',
                                        ];
                                    @endphp
                                    <span class="px-3 py-1 text-xs rounded-full font-medium {{ $statusColors[$statusValue] ?? 'bg-[#eef1e8] text-[#4E653D]' }}">
                                        {{ __('app.' . $statusValue) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-[#7a8f6a]">{{ __('app.no_bookings_found') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
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
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: '{{ __('app.room_bookings_label') }}',
                    data: data,
                    backgroundColor: '#4E653D',
                    borderRadius: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: { duration: 400 },
                interaction: { mode: 'index', intersect: false },
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 }, title: { display: true, text: '{{ __('app.bookings_axis') }}' } },
                    x: { title: { display: true, text: '{{ __('app.date') }}' } }
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
