<div class="min-h-screen bg-gray-50">
    <main class="max-w-7xl mx-auto px-4 sm:px-6 py-8 space-y-8">
        {{-- HEADER --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Guestbook Statistics</h1>
                <p class="text-sm text-gray-500">Track visitor activity and trends</p>
            </div>
            <div class="flex gap-2">
                <button wire:click="setTimeRange('7days')" 
                    class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $timeRange === '7days' ? 'bg-blue-600 text-white' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50' }}">
                    7 Days
                </button>
                <button wire:click="setTimeRange('30days')" 
                    class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $timeRange === '30days' ? 'bg-blue-600 text-white' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50' }}">
                    30 Days
                </button>
                <button wire:click="setTimeRange('90days')" 
                    class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $timeRange === '90days' ? 'bg-blue-600 text-white' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50' }}">
                    90 Days
                </button>
            </div>
        </div>

        {{-- STATS --}}
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($stats as $stat)
                @php
                    $colors = [
                        'blue' => 'text-blue-600',
                        'yellow' => 'text-yellow-600',
                        'green' => 'text-green-600',
                        'purple' => 'text-purple-600',
                    ];
                @endphp
                <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm hover:shadow-lg transition">
                    <p class="text-sm font-medium text-gray-500">{{ $stat['label'] }}</p>
                    <h2 class="text-3xl font-bold mt-2 {{ $colors[$stat['color']] }}">{{ $stat['value'] }}</h2>
                </div>
            @endforeach
        </section>

        {{-- CHART --}}
        <div class="bg-white border border-gray-200 p-6 rounded-2xl shadow-sm">
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Daily Visitor Trend</h3>
                <button wire:click="toggleList" 
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium transition">
                    {{ $showList ? 'Hide List' : 'Show List' }}
                </button>
            </div>
            <div wire:ignore style="position: relative; height: 400px;">
                <canvas id="guestbookChart"></canvas>
            </div>
        </div>

        {{-- GUESTBOOK LIST --}}
        @if($showList)
            <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b bg-gray-50">
                    <h3 class="font-semibold text-gray-900">Recent Visitors</h3>
                </div>
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500 uppercase text-xs border-b">
                        <tr>
                            <th class="px-6 py-3 text-left font-medium">ID</th>
                            <th class="px-6 py-3 text-left font-medium">Name</th>
                            <th class="px-6 py-3 text-left font-medium">Institution</th>
                            <th class="px-6 py-3 text-left font-medium">Purpose</th>
                            <th class="px-6 py-3 text-left font-medium">Check In</th>
                            <th class="px-6 py-3 text-left font-medium">Check Out</th>
                            <th class="px-6 py-3 text-left font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($guestbooks as $guest)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-gray-900">#{{ $guest->guestbook_id }}</td>
                                <td class="px-6 py-4 text-gray-900 font-medium">{{ $guest->name }}</td>
                                <td class="px-6 py-4 text-gray-900">{{ $guest->instansi ?? 'N/A' }}</td>
                                <td class="px-6 py-4 text-gray-900">{{ Str::limit($guest->keperluan, 30) }}</td>
                                <td class="px-6 py-4 text-gray-900">{{ $guest->jam_in ?? '-' }}</td>
                                <td class="px-6 py-4 text-gray-900">{{ $guest->jam_out ?? '-' }}</td>
                                <td class="px-6 py-4">
                                    @if($guest->jam_out)
                                        <span class="px-3 py-1 text-xs rounded-full font-medium bg-green-100 text-green-700">
                                            Checked Out
                                        </span>
                                    @elseif($guest->jam_in)
                                        <span class="px-3 py-1 text-xs rounded-full font-medium bg-yellow-100 text-yellow-700">
                                            In Building
                                        </span>
                                    @else
                                        <span class="px-3 py-1 text-xs rounded-full font-medium bg-gray-100 text-gray-700">
                                            Registered
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-gray-500">No visitors found</td>
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
    function buildGuestbookChart() {
        const ctx = document.getElementById('guestbookChart');
        if (!ctx) return;

        const labels = @json($labels);
        const data = @json($data);

        if (window.guestbookChart && typeof window.guestbookChart.destroy === 'function') {
            window.guestbookChart.destroy();
        }

        window.guestbookChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Visitors',
                    data: data,
                    backgroundColor: '#8b5cf6',
                    borderRadius: 8,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: { duration: 600 },
                scales: {
                    y: { 
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(buildGuestbookChart, 100);
    });
    
    document.addEventListener('livewire:navigated', function() {
        setTimeout(buildGuestbookChart, 100);
    });

    if (typeof Livewire !== 'undefined') {
        Livewire.hook('commit', ({ component, commit, respond, succeed, fail }) => {
            succeed(({ snapshot, effect }) => {
                setTimeout(buildGuestbookChart, 100);
            });
        });
    }
</script>
