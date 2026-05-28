<div class="min-h-screen bg-[#f5f7f2]">
    <main class="max-w-7xl mx-auto px-4 sm:px-6 py-8 space-y-8">

        {{-- HEADER --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-[#2d3a24]">{{ __('app.guestbook_stats_title') }}</h1>
                <p class="text-sm text-[#7a8f6a]">{{ __('app.guestbook_stats_sub') }}</p>
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
                        'green'  => 'text-green-600',
                        'purple' => 'text-[#5a6e4a]',
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
                <h3 class="text-lg font-semibold text-[#2d3a24]">{{ __('app.daily_visitor_trend') }}</h3>
                <button wire:click="toggleList"
                    class="px-4 py-2 bg-[#4A2F24] text-white rounded-lg hover:bg-[#3d2720] text-sm font-medium transition">
                    {{ $showList ? __('app.hide_list') : __('app.show_list') }}
                </button>
            </div>
            <div wire:ignore style="position: relative; height: 400px;">
                <canvas id="guestbookChart"></canvas>
            </div>
        </div>

        {{-- GUESTBOOK LIST --}}
        @if($showList)
            <div class="bg-white border border-[#d4dfc8] rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b bg-[#f0f4eb]">
                    <h3 class="font-semibold text-[#2d3a24]">{{ __('app.recent_visitors') }}</h3>
                </div>
                <table class="w-full text-sm">
                    <thead class="bg-[#f0f4eb] text-[#7a8f6a] uppercase text-xs border-b">
                        <tr>
                            <th class="px-6 py-3 text-left font-medium">ID</th>
                            <th class="px-6 py-3 text-left font-medium">{{ __('app.name') }}</th>
                            <th class="px-6 py-3 text-left font-medium">{{ __('app.institution') }}</th>
                            <th class="px-6 py-3 text-left font-medium">{{ __('app.visit_purpose') }}</th>
                            <th class="px-6 py-3 text-left font-medium">{{ __('app.check_in') }}</th>
                            <th class="px-6 py-3 text-left font-medium">{{ __('app.check_out') }}</th>
                            <th class="px-6 py-3 text-left font-medium">{{ __('app.status') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#d4dfc8]">
                        @forelse($guestbooks as $guest)
                            <tr class="hover:bg-[#f0f4eb]">
                                <td class="px-6 py-4 text-[#2d3a24]">#{{ $guest->guestbook_id }}</td>
                                <td class="px-6 py-4 text-[#2d3a24] font-medium">{{ $guest->name }}</td>
                                <td class="px-6 py-4 text-[#2d3a24]">{{ $guest->instansi ?? 'N/A' }}</td>
                                <td class="px-6 py-4 text-[#2d3a24]">{{ Str::limit($guest->keperluan, 30) }}</td>
                                <td class="px-6 py-4 text-[#2d3a24]">{{ $guest->jam_in ?? '-' }}</td>
                                <td class="px-6 py-4 text-[#2d3a24]">{{ $guest->jam_out ?? '-' }}</td>
                                <td class="px-6 py-4">
                                    @if($guest->jam_out)
                                        <span class="px-3 py-1 text-xs rounded-full font-medium bg-green-100 text-green-700">
                                            {{ __('app.checked_out') }}
                                        </span>
                                    @elseif($guest->jam_in)
                                        <span class="px-3 py-1 text-xs rounded-full font-medium bg-yellow-100 text-yellow-700">
                                            {{ __('app.in_building') }}
                                        </span>
                                    @else
                                        <span class="px-3 py-1 text-xs rounded-full font-medium bg-[#eef1e8] text-[#4E653D]">
                                            {{ __('app.registered_status') }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-[#7a8f6a]">{{ __('app.no_visitors_found') }}</td>
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
    function buildGuestbookChart(labels, data) {
        const ctx = document.getElementById('guestbookChart');
        if (!ctx) return;

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
                    y: { beginAtZero: true, ticks: { stepSize: 1 }, title: { display: true, text: 'Visitors' } },
                    x: { title: { display: true, text: 'Date' } }
                }
            }
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        buildGuestbookChart(@json($labels), @json($data));
    });

    document.addEventListener('livewire:init', () => {
        Livewire.on('guestbook-chart-updated', ({ labels, data }) => {
            buildGuestbookChart(labels, data);
        });
    });
</script>
