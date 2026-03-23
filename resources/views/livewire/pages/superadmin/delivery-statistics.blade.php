<div class="min-h-screen bg-gray-50">
    <main class="px-4 sm:px-6 py-6 space-y-8">
        {{-- HEADER --}}
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl text-gray-500 font-semibold">Delivery Statistics</h1>
                <p class="text-sm text-gray-500">Track package delivery trends</p>
            </div>
            <div class="flex gap-2">
                <button wire:click="setTimeRange('7days')" 
                    class="px-4 py-2 rounded-lg text-sm transition {{ $timeRange === '7days' ? 'bg-blue-600 text-gray-500' : 'bg-white text-black' }}">
                    7 Days
                </button>
                <button wire:click="setTimeRange('30days')" 
                    class="px-4 py-2 rounded-lg text-sm transition {{ $timeRange === '30days' ? 'bg-blue-600 text-gray-500' : 'bg-white text-black' }}">
                    30 Days
                </button>
                <button wire:click="setTimeRange('90days')" 
                    class="px-4 py-2 rounded-lg text-sm transition {{ $timeRange === '90days' ? 'bg-blue-600 text-gray-500' : 'bg-white text-black' }}">
                    90 Days
                </button>
            </div>
        </div>

        {{-- STATS --}}
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($stats as $stat)
                @php
                    $colors = [
                        'blue' => 'bg-blue-50 text-blue-600',
                        'yellow' => 'bg-yellow-50 text-yellow-600',
                        'green' => 'bg-green-50 text-green-600',
                        'purple' => 'bg-purple-50 text-purple-600',
                    ];
                @endphp
                <div class="bg-white rounded-2xl p-5 shadow-sm hover:shadow-lg transition">
                    <p class="text-sm text-gray-500">{{ $stat['label'] }}</p>
                    <h2 class="text-3xl text-gray-500 font-bold mt-2 {{ $colors[$stat['color']] }}">{{ $stat['value'] }}</h2>
                </div>
            @endforeach
        </section>

        {{-- CHART --}}
        <div class="bg-white p-6 rounded-2xl shadow-sm">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Daily Delivery Trend</h3>
                <button wire:click="toggleList" 
                    class="px-4 py-2 bg-blue-100 text-gray-700 rounded-lg hover:bg-blue-200 text-sm">
                    {{ $showList ? 'Hide List' : 'Show List' }}
                </button>
            </div>
            <canvas id="deliveryChart"></canvas>
        </div>

        {{-- DELIVERY LIST --}}
        @if($showList)
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b bg-gray-50">
                    <h3 class="font-semibold">Recent Deliveries</h3>
                </div>
                <table class="w-full">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Recipient</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($deliveries as $delivery)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm text-gray-500">#{{ $delivery->delivery_id }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 font-medium">{{ $delivery->recipient_name ?? 'N/A' }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-full text-xs text-gray-500 font-medium {{ $delivery->status === 'completed' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                        {{ ucfirst($delivery->status ?? 'pending') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $delivery->created_at?->format('M d, Y H:i') }}</td>
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

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @script
    <script>
        function buildDeliveryChart() {
            const ctx = document.getElementById('deliveryChart')?.getContext('2d');
            if (!ctx) return;

            const labels = @json($labels);
            const data = @json($data);

            if (window.deliveryChart) window.deliveryChart.destroy();

            window.deliveryChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Deliveries',
                        data: data,
                        backgroundColor: '#3b82f6',
                        borderRadius: 8,
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

        document.addEventListener('DOMContentLoaded', buildDeliveryChart);
        document.addEventListener('livewire:updated', buildDeliveryChart);
    </script>
    @endscript
</div>
