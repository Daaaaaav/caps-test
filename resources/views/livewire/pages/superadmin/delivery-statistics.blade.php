<div class="min-h-screen bg-gray-50">
    <main class="max-w-7xl mx-auto px-4 sm:px-6 py-8 space-y-8">

        {{-- HEADER --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Delivery Statistics</h1>
                <p class="text-sm text-gray-500">Track package and document delivery trends</p>
            </div>
            <div class="flex gap-2">
                <button wire:click="setTimeRange('7days')"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $timeRange === '7days' ? 'bg-gray-900 text-white' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50' }}">
                    7 Days
                </button>
                <button wire:click="setTimeRange('30days')"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $timeRange === '30days' ? 'bg-gray-900 text-white' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50' }}">
                    30 Days
                </button>
                <button wire:click="setTimeRange('90days')"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $timeRange === '90days' ? 'bg-gray-900 text-white' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50' }}">
                    90 Days
                </button>
            </div>
        </div>

        {{-- STATS --}}
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($stats as $stat)
                @php
                    $colors = [
                        'blue'   => 'text-gray-900',
                        'yellow' => 'text-yellow-600',
                        'purple' => 'text-gray-600',
                        'green'  => 'text-green-600',
                    ];
                @endphp
                <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm hover:shadow-lg transition">
                    <p class="text-sm font-medium text-gray-500">{{ $stat['label'] }}</p>
                    <h2 class="text-3xl font-bold mt-2 {{ $colors[$stat['color']] ?? 'text-gray-900' }}">{{ $stat['value'] }}</h2>
                </div>
            @endforeach
        </section>

        {{-- CHART --}}
        <div class="bg-white border border-gray-200 p-6 rounded-2xl shadow-sm">
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Daily Delivery Trend</h3>
                <button wire:click="toggleList"
                    class="px-4 py-2 bg-gray-900 text-white rounded-lg hover:bg-gray-800 text-sm font-medium transition">
                    {{ $showList ? 'Hide List' : 'Show List' }}
                </button>
            </div>
            <div wire:ignore style="position: relative; height: 400px;">
                <canvas id="deliveryChart"></canvas>
            </div>
        </div>

        {{-- DELIVERY LIST --}}
        @if($showList)
            <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b bg-gray-50">
                    <h3 class="font-semibold text-gray-900">Recent Deliveries</h3>
                </div>
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500 uppercase text-xs border-b">
                        <tr>
                            <th class="px-6 py-3 text-left font-medium">ID</th>
                            <th class="px-6 py-3 text-left font-medium">Recipient</th>
                            <th class="px-6 py-3 text-left font-medium">Status</th>
                            <th class="px-6 py-3 text-left font-medium">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($deliveries as $delivery)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-gray-900">#{{ $delivery->delivery_id }}</td>
                                <td class="px-6 py-4 text-gray-900 font-medium">{{ $delivery->nama_penerima ?? 'N/A' }}</td>
                                <td class="px-6 py-4">
                                    @php
                                        $s = $delivery->status ?? 'pending';
                                        $statusColors = [
                                            'pending' => 'bg-yellow-100 text-yellow-700',
                                            'stored'  => 'bg-gray-100 text-gray-700',
                                            'done'    => 'bg-green-100 text-green-700',
                                        ];
                                    @endphp
                                    <span class="px-3 py-1 rounded-full text-xs font-medium {{ $statusColors[$s] ?? 'bg-gray-100 text-gray-700' }}">
                                        {{ ucfirst($s) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-600">{{ $delivery->created_at?->format('M d, Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-gray-500">No deliveries found</td>
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
                    label: 'Deliveries',
                    data: data,
                    backgroundColor: '#4b5563',
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
                    y: { beginAtZero: true, ticks: { stepSize: 1 }, title: { display: true, text: 'Deliveries' } },
                    x: { title: { display: true, text: 'Date' } }
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
