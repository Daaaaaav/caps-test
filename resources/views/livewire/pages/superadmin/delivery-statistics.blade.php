<div class="min-h-screen bg-[#f5f7f2]">
    <main class="max-w-7xl mx-auto px-4 sm:px-6 py-8 space-y-8">

        {{-- HEADER --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-[#2d3a24]">{{ __('app.delivery_stats_title') }}</h1>
                <p class="text-sm text-[#7a8f6a]">{{ __('app.delivery_stats_sub') }}</p>
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

        {{-- STATS --}}
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($stats as $stat)
                @php
                    $colors = [
                        'blue'   => 'text-[#2d3a24]',
                        'yellow' => 'text-yellow-600',
                        'purple' => 'text-[#5a6e4a]',
                        'green'  => 'text-green-600',
                    ];
                @endphp
                <div class="bg-white border border-[#d4dfc8] rounded-2xl p-5 shadow-sm hover:shadow-lg transition">
                    <p class="text-sm font-medium text-[#7a8f6a]">{{ $stat['label'] }}</p>
                    <h2 class="text-3xl font-bold mt-2 {{ $colors[$stat['color']] ?? 'text-[#2d3a24]' }}">{{ $stat['value'] }}</h2>
                </div>
            @endforeach
        </section>

        {{-- CHART --}}
        <div class="bg-white border border-[#d4dfc8] p-6 rounded-2xl shadow-sm">
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-4">
                <h3 class="text-lg font-semibold text-[#2d3a24]">{{ __('app.daily_delivery_trend') }}</h3>
                <button wire:click="toggleList"
                    class="px-4 py-2 bg-[#4A2F24] text-white rounded-lg hover:bg-[#3d2720] text-sm font-medium transition">
                    {{ $showList ? __('app.hide_list') : __('app.show_list') }}
                </button>
            </div>
            <div wire:ignore style="position: relative; height: 400px;">
                <canvas id="deliveryChart"></canvas>
            </div>
        </div>

        {{-- DELIVERY LIST --}}
        @if($showList)
            <div class="bg-white border border-[#d4dfc8] rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b bg-[#f0f4eb]">
                    <h3 class="font-semibold text-[#2d3a24]">{{ __('app.recent_deliveries') }}</h3>
                </div>
                <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[500px]">
                    <thead class="bg-[#f0f4eb] text-[#7a8f6a] uppercase text-xs border-b">
                        <tr>
                            <th class="px-6 py-3 text-left font-medium">ID</th>
                            <th class="px-6 py-3 text-left font-medium">{{ __('app.recipient') }}</th>
                            <th class="px-6 py-3 text-left font-medium">{{ __('app.status') }}</th>
                            <th class="px-6 py-3 text-left font-medium">{{ __('app.date') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#d4dfc8]">
                        @forelse($deliveries as $delivery)
                            <tr class="hover:bg-[#f0f4eb]">
                                <td class="px-6 py-4 text-[#2d3a24]">#{{ $delivery->delivery_id }}</td>
                                <td class="px-6 py-4 text-[#2d3a24] font-medium">{{ $delivery->nama_penerima ?? 'N/A' }}</td>
                                <td class="px-6 py-4">
                                    @php
                                        $s = $delivery->status ?? 'pending';
                                        $statusColors = [
                                            'pending' => 'bg-yellow-100 text-yellow-700',
                                            'stored'  => 'bg-[#eef1e8] text-[#4E653D]',
                                            'done'    => 'bg-green-100 text-green-700',
                                        ];
                                        $statusLabels = [
                                            'pending' => __('app.pending'),
                                            'stored'  => __('app.stored'),
                                            'done'    => __('app.done'),
                                        ];
                                    @endphp
                                    <span class="px-3 py-1 rounded-full text-xs font-medium {{ $statusColors[$s] ?? 'bg-[#eef1e8] text-[#4E653D]' }}">
                                        {{ $statusLabels[$s] ?? $s }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-[#5a6e4a]">{{ $delivery->created_at?->format('d/m/Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-[#7a8f6a]">{{ __('app.no_deliveries_found') }}</td>
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
    function buildDeliveryChart(labels, data) {
        const ctx = document.getElementById('deliveryChart');
        if (!ctx) return;

        if (window.deliveryChart && typeof window.deliveryChart.destroy === 'function') {
            window.deliveryChart.destroy();
        }

        window.deliveryChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: '{{ __('app.deliveries') }}',
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
                    y: { beginAtZero: true, ticks: { stepSize: 1 }, title: { display: true, text: '{{ __('app.deliveries') }}' } },
                    x: { title: { display: true, text: '{{ __('app.date_label') }}' } }
                }
            }
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        buildDeliveryChart(@json($labels), @json($data));
    });

    document.addEventListener('livewire:init', () => {
        Livewire.on('delivery-chart-updated', ({ labels, data }) => {
            buildDeliveryChart(labels, data);
        });
    });
</script>
