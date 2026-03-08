<div class="bg-gray-50" wire:poll.2000ms.keep-alive>
    <main class="px-4 sm:px-6 py-6">
        <div class="space-y-8">

            {{-- Greeting --}}
            <div class="rounded-2xl bg-gradient-to-r from-gray-900 to-black text-white p-6 shadow-2xl">
                <h2 class="text-lg font-semibold">Selamat Datang di Dashboard Receptionist</h2>
                <p class="text-sm text-gray-300">
                    Berikut total data 7 hari terakhir dari setiap modul
                </p>
            </div>

            {{-- Statistics Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                {{-- Total Room Bookings --}}
                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Room Bookings (7 hari)</p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ $weeklyRoomBookingsCount }}
                            </p>
                            <p class="text-xs text-green-600 mt-1">+12% vs last week</p>
                        </div>
                        <div class="p-3 bg-gray-100 rounded-lg">
                            <x-heroicon-o-calendar-days class="w-6 h-6 text-gray-700" />
                        </div>
                    </div>
                </div>

                {{-- Vehicle Bookings --}}
                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Vehicle Bookings (7 hari)</p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ $weeklyVehicleBookingsCount }}
                            </p>
                            <p class="text-xs text-green-600 mt-1">+8% vs last week</p>
                        </div>
                        <div class="p-3 bg-gray-100 rounded-lg">
                            <x-heroicon-o-bolt class="w-6 h-6 text-gray-700" />
                        </div>
                    </div>
                </div>

                {{-- Guests --}}
                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Guest Visits (7 hari)</p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ $weeklyGuestsCount }}
                            </p>
                            <p class="text-xs text-green-600 mt-1">+15% vs last week</p>
                        </div>
                        <div class="p-3 bg-gray-100 rounded-lg">
                            <x-heroicon-o-user-group class="w-6 h-6 text-gray-700" />
                        </div>
                    </div>
                </div>

                {{-- Documents --}}
                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Documents/Packages (7 hari)</p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ $weeklyDocsCount }}
                            </p>
                            <p class="text-xs text-green-600 mt-1">+5% vs last week</p>
                        </div>
                        <div class="p-3 bg-gray-100 rounded-lg">
                            <x-heroicon-o-archive-box class="w-6 h-6 text-gray-700" />
                        </div>
                    </div>
                </div>
            </div>

            {{-- Charts Section --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Activity Chart (new style like report page) --}}
                <div class="lg:col-span-2 bg-white rounded-2xl p-6 shadow-sm border border-gray-200">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-base font-semibold text-gray-900 flex items-center gap-2">
                            <x-heroicon-o-chart-bar class="h-5 w-5 text-gray-900" />
                            Weekly Activity – Room / Vehicle / DocPac / Guestbook
                        </h3>
                        <p class="text-xs text-gray-500">
                            7 hari terakhir (dummy data)
                        </p>
                    </div>

                    <div class="h-[320px]" wire:ignore>
                        <canvas id="activityChart" class="w-full" style="max-height:320px"></canvas>
                    </div>
                </div>

                {{-- Status Distribution --}}
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-200">
                    <h3 class="text-base font-semibold text-gray-900 mb-4">Status Distribution</h3>
                    <div class="space-y-4">
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm font-medium text-gray-700">Approved</span>
                                <span class="text-sm font-bold text-gray-900">65%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-gray-900 h-2 rounded-full" style="width: 65%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm font-medium text-gray-700">Pending</span>
                                <span class="text-sm font-bold text-gray-900">25%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-gray-600 h-2 rounded-full" style="width: 25%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm font-medium text-gray-700">Rejected</span>
                                <span class="text-sm font-bold text-gray-900">10%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-gray-400 h-2 rounded-full" style="width: 10%"></div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-6 p-4 bg-gray-50 rounded-xl border border-gray-200">
                        <p class="text-xs text-gray-600 mb-1">Total Requests This Month</p>
                        <p class="text-2xl font-bold text-gray-900">247</p>
                        <p class="text-xs text-gray-600 mt-1">↑ 18% from last month</p>
                    </div>
                </div>
            </div>

        </div>
    </main>

    {{-- Chart.js v4 seperti di report page --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    @verbatim
        <script>
            let __activityChart;

            function rebuildActivityChart() {
                const canvas = document.getElementById('activityChart');
                if (!canvas) return;

                const ctx = canvas.getContext('2d');

                // Dummy weekly data – bisa kamu ganti dari @json kalau nanti mau dinamis
                const weekly = {
                    labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                    room: [5, 9, 7, 12, 10, 8, 11],
                    vehicle: [2, 4, 3, 6, 5, 4, 5],
                    docpac: [1, 3, 2, 4, 3, 2, 3],
                    guest: [4, 6, 5, 9, 8, 7, 9],
                };

                if (__activityChart) {
                    __activityChart.destroy();
                }

                const paletteLine = {
                    room: '#1d4ed8', // blue
                    vehicle: '#059669', // emerald
                    docpac: '#f59e0b', // amber
                    guest: '#7c3aed', // violet
                };

                const datasets = [
                    {
                        label: 'Room',
                        data: weekly.room,
                        borderColor: paletteLine.room,
                        tension: 0.35,
                        pointRadius: 3,
                        fill: false,
                    },
                    {
                        label: 'Vehicle',
                        data: weekly.vehicle,
                        borderColor: paletteLine.vehicle,
                        tension: 0.35,
                        pointRadius: 3,
                        fill: false,
                    },
                    {
                        label: 'DocPac',
                        data: weekly.docpac,
                        borderColor: paletteLine.docpac,
                        tension: 0.35,
                        pointRadius: 3,
                        fill: false,
                    },
                    {
                        label: 'Guestbook',
                        data: weekly.guest,
                        borderColor: paletteLine.guest,
                        tension: 0.35,
                        pointRadius: 3,
                        fill: false,
                    },
                ];

                __activityChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: weekly.labels,
                        datasets: datasets,
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                            },
                            tooltip: {
                                mode: 'index',
                                intersect: false,
                            },
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0,
                                    stepSize: 1,
                                },
                                grid: {
                                    color: 'rgba(0,0,0,0.05)',
                                },
                            },
                            x: {
                                grid: {
                                    display: false,
                                },
                            },
                        },
                        interaction: {
                            mode: 'nearest',
                            intersect: false,
                        },
                    },
                });
            }

            document.addEventListener('DOMContentLoaded', () => {
                rebuildActivityChart();
            });

            document.addEventListener('livewire:load', () => {
                rebuildActivityChart();
            });

            document.addEventListener('livewire:navigated', () => {
                rebuildActivityChart();
            });
        </script>
    @endverbatim
</div>