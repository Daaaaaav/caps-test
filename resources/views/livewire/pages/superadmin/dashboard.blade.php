{{-- resources/views/livewire/pages/superadmin/dashboard.blade.php --}}
<div class="min-h-screen bg-gray-50" wire:poll.10000ms="tick">
    <main class="px-4 sm:px-6 py-6 space-y-8">

        {{-- HERO --}}
        <div
            class="rounded-2xl bg-gradient-to-r from-gray-900 to-black text-white p-6 sm:p-8 shadow-2xl flex items-center gap-3">
            <x-heroicon-o-chart-bar class="w-8 h-8 text-white/80" />
            <div>
                <h2 class="text-lg sm:text-xl font-semibold">Welcome, {{ $admin_name }}!</h2>
                <p class="text-sm text-white/80">Here’s an overview of this year’s activity.</p>
            </div>
        </div>

        {{-- STATS (No Company) --}}
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($stats as $s)
                <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-5 text-center">
                    <x-heroicon-o-chart-bar class="w-6 h-6 mx-auto mb-1 text-gray-600" />
                    <p class="text-gray-500 text-sm">{{ $s['label'] }}</p>
                    <h3 class="text-2xl font-semibold text-gray-900 mt-2">{{ number_format($s['value']) }}</h3>
                </div>
            @endforeach
        </section>

        {{-- MONTHLY LINE CHART --}}
        <section class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6">
            <h3 class="flex items-center gap-2 text-lg font-semibold text-gray-900 mb-4">
                <x-heroicon-o-calendar class="w-5 h-5 text-gray-700" />
                Monthly Statistics
            </h3>
            <div wire:ignore>
                <canvas id="bookingChart" class="w-full" style="max-height:380px"></canvas>
            </div>
        </section>

        {{-- ===== TICKETING OVERVIEW (3 CHARTS) ===== --}}
        <section class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Priority Distribution (Bar) --}}
            <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="flex items-center gap-2 text-base font-semibold text-gray-900">
                        <x-heroicon-o-arrow-trending-up class="w-5 h-5 text-gray-700" />
                        Ticket Priority Distribution
                    </h3>
                    <span class="text-xs text-gray-500">Low=1, Med=2, High=3</span>
                </div>
                <div wire:ignore>
                    <canvas id="ticketPriorityBar" style="max-height:320px"></canvas>
                </div>
            </div>

            {{-- Status Distribution (Doughnut) --}}
            <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6">
                <h3 class="flex items-center gap-2 text-base font-semibold text-gray-900 mb-4">
                    <x-heroicon-o-chart-pie class="w-5 h-5 text-gray-700" />
                    Ticket Status Distribution
                </h3>
                <div wire:ignore>
                    <canvas id="ticketStatusPie" style="max-height:320px"></canvas>
                </div>
            </div>

            {{-- Monthly Priority Average (Line) --}}
            <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6">
                <h3 class="flex items-center gap-2 text-base font-semibold text-gray-900 mb-4">
                    <x-heroicon-o-presentation-chart-line class="w-5 h-5 text-gray-700" />
                    Monthly Priority Average (This Year)
                </h3>
                <div wire:ignore>
                    <canvas id="ticketPriorityAvg" style="max-height:320px"></canvas>
                </div>
            </div>
        </section>

    </main>

    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    <script>
        function buildMonthlyChart() {
            const ctx = document.getElementById('bookingChart')?.getContext('2d');
            if (!ctx) return;
            if (window.__dashboardChart) window.__dashboardChart.destroy();

            const labels = @json($chartData['labels']);
            const room = @json($chartData['room']);
            const vehicle = @json($chartData['vehicle']);
            const ticket = @json($chartData['ticket']);

            window.__dashboardChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels,
                    datasets: [
                        { label: 'Room Bookings', data: room, borderColor: '#1d4ed8', backgroundColor: 'rgba(29,78,216,.08)', tension: 0.35, pointRadius: 3 },
                        { label: 'Vehicle Bookings', data: vehicle, borderColor: '#059669', backgroundColor: 'rgba(5,150,105,.08)', tension: 0.35, pointRadius: 3 },
                        { label: 'Support Tickets', data: ticket, borderColor: '#dc2626', backgroundColor: 'rgba(220,38,38,.08)', tension: 0.35, pointRadius: 3 },
                    ]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'top' } }, scales: { y: { beginAtZero: true } } }
            });
        }

        function buildTicketCharts() {
            if (window.__ticketPriorityBar) window.__ticketPriorityBar.destroy();
            if (window.__ticketStatusPie) window.__ticketStatusPie.destroy();
            if (window.__ticketPriorityAvg) window.__ticketPriorityAvg.destroy();

            // Priority Bar
            const bar = document.getElementById('ticketPriorityBar')?.getContext('2d');
            if (bar) {
                const data = @json(array_values($ticketCharts['priorityCounts']));
                window.__ticketPriorityBar = new Chart(bar, {
                    type: 'bar',
                    data: { labels: ['Low', 'Medium', 'High'], datasets: [{ data, backgroundColor: ['#93c5fd', '#fbbf24', '#f87171'] }] },
                    options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
                });
            }

            // Status Pie
            const pie = document.getElementById('ticketStatusPie')?.getContext('2d');
            if (pie) {
                const map = @json($ticketCharts['statusCounts']);
                const data = ['OPEN', 'IN_PROGRESS', 'RESOLVED', 'CLOSED'].map(k => map[k] || 0);
                window.__ticketStatusPie = new Chart(pie, {
                    type: 'doughnut',
                    data: { labels: ['OPEN', 'IN_PROGRESS', 'RESOLVED', 'CLOSED'], datasets: [{ data, backgroundColor: ['#60a5fa', '#f59e0b', '#10b981', '#9ca3af'] }] },
                    options: { plugins: { legend: { position: 'bottom' } }, cutout: '55%' }
                });
            }

            // Avg Priority Line
            const avg = document.getElementById('ticketPriorityAvg')?.getContext('2d');
            if (avg) {
                const data = @json($ticketCharts['avgPriority']);
                const labels = @json($ticketCharts['labels']);
                window.__ticketPriorityAvg = new Chart(avg, {
                    type: 'line',
                    data: { labels, datasets: [{ label: 'Avg Priority', data, borderColor: '#312e81', backgroundColor: 'rgba(49,46,129,.08)', tension: .35, pointRadius: 3 }] },
                    options: { responsive: true, scales: { y: { min: 0, max: 3, ticks: { stepSize: 1, callback: (v) => ({ 0: '', 1: 'Low', 2: 'Med', 3: 'High' }[v] || v) } } } }
                });
            }
        }

        document.addEventListener('DOMContentLoaded', () => { buildMonthlyChart(); buildTicketCharts(); });
        document.addEventListener('livewire:load', () => { buildMonthlyChart(); buildTicketCharts(); });
        document.addEventListener('livewire:navigated', () => { buildMonthlyChart(); buildTicketCharts(); });
    </script>
</div>