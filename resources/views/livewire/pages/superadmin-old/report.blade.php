<div class="min-h-screen bg-gray-50">
    <!-- Download overlay -->
    <div id="downloadOverlay" class="fixed inset-0 z-[100] hidden">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
        <div class="relative h-full w-full flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-xs text-center">
                <div class="mx-auto mb-3 h-10 w-10 animate-spin rounded-full border-4 border-gray-200 border-t-gray-900"></div>
                <p class="font-semibold text-gray-900">Menyiapkan PDF…</p>
                <p class="text-xs text-gray-500 mt-1">Tunggu sampai dialog download muncul.</p>
                <button id="hideOverlay" type="button" class="mt-4 text-xs text-gray-600 underline">Sembunyikan</button>
            </div>
        </div>
    </div>

    <main class="px-4 sm:px-6 py-6 space-y-8">

        {{-- Header with company --}}
        <div class="rounded-2xl bg-gradient-to-r from-gray-900 to-black text-white p-6 sm:p-8 shadow-2xl">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-3">
                    @if(!empty($company['image']))
                        <img src="{{ $company['image'] }}" alt="Logo" class="h-10 w-10 rounded-full object-cover border border-white/20">
                    @else
                        <div class="h-10 w-10 rounded-full bg-white/10 border border-white/20 grid place-items-center">
                            <x-heroicon-o-building-office-2 class="h-5 w-5" />
                        </div>
                    @endif
                    <div>
                        <h2 class="text-lg sm:text-xl font-semibold tracking-wide">
                            {{ $company['company_name'] ?? '—' }} — Reports &amp; Evaluation
                        </h2>
                        <p class="text-xs text-white/70 flex items-center gap-1">
                            <x-heroicon-o-calendar class="h-4 w-4" />
                            Tahun aktif: {{ $year }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <select wire:model.live="year"
                            class="h-10 rounded-xl border-2 border-white/20 bg-white/10 text-white px-3">
                        @for($y = now()->year; $y >= now()->year - 9; $y--)
                            <option value="{{ $y }}" class="text-gray-900">{{ $y }}</option>
                        @endfor
                    </select>

                    <button id="downloadPdfBtn"
                        class="px-4 py-2 text-sm rounded-xl bg-white text-gray-900 hover:bg-gray-100 font-semibold shadow inline-flex items-center gap-2 disabled:opacity-60 disabled:cursor-not-allowed">
                        <svg id="btnSpinner" class="hidden h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity=".25" stroke-width="4"></circle>
                            <path d="M22 12a10 10 0 0 1-10 10" stroke="currentColor" stroke-width="4"></path>
                        </svg>
                        <x-heroicon-o-arrow-down-tray id="btnIcon" class="h-4 w-4" />
                        <span id="btnLabel">Download PDF</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- KPI Summary --}}
        <section class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
                <div class="flex items-center gap-2 text-gray-600">
                    <x-heroicon-o-sparkles class="h-5 w-5 text-gray-900" />
                    <p class="text-sm">Tahun Dipilih</p>
                </div>
                <h3 class="text-2xl font-semibold text-gray-900 mt-1">{{ $summary['selected_year'] }}</h3>
            </div>

            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
                <div class="flex items-center gap-2 text-gray-600">
                    <x-heroicon-o-presentation-chart-line class="h-5 w-5 text-emerald-600" />
                    <p class="text-sm">Total Aktivitas</p>
                </div>
                <h3 class="text-2xl font-semibold text-gray-900 mt-1">{{ number_format($summary['total_activity']) }}</h3>
            </div>

            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
                <div class="flex items-center gap-2 text-gray-600">
                    <x-heroicon-o-arrow-trending-up class="h-5 w-5 text-indigo-600" />
                    <p class="text-sm">Bulan Tersibuk</p>
                </div>
                <h3 class="text-2xl font-semibold text-gray-900 mt-1">
                    {{ $summary['busiest_month'] }} ({{ number_format($summary['busiest_total']) }})
                </h3>
            </div>
        </section>

        {{-- SLA Snapshot (Blade Icons + warna) --}}
        @php
            $prio = $ticketPerf['by_priority'] ?? [];
            $cards = [
                'high' => ['label'=>'High','bg'=>'bg-red-50','text'=>'text-red-700','ring'=>'ring-red-200','icon'=>'exclamation-triangle','iconClass'=>'text-red-600'],
                'medium' => ['label'=>'Medium','bg'=>'bg-yellow-50','text'=>'text-yellow-700','ring'=>'ring-yellow-200','icon'=>'clock','iconClass'=>'text-yellow-600'],
                'low' => ['label'=>'Low','bg'=>'bg-emerald-50','text'=>'text-emerald-700','ring'=>'ring-emerald-200','icon'=>'check-circle','iconClass'=>'text-emerald-600'],
            ];
            $badge = fn($label,$bg,$text) => "<span class='px-2 py-0.5 rounded-full text-xs font-semibold {$bg} {$text}'>$label</span>";
        @endphp

        <section class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-semibold text-gray-900 flex items-center gap-2">
                    <x-heroicon-o-clock class="h-5 w-5 text-gray-900" />
                    SLA Snapshot (Tahun {{ $year }})
                </h3>
                <div class="text-xs text-gray-500 flex items-center gap-3">
                    <span class="inline-flex items-center gap-1">
                        <span class="h-2.5 w-2.5 rounded-full bg-red-500"></span> High
                    </span>
                    <span class="inline-flex items-center gap-1">
                        <span class="h-2.5 w-2.5 rounded-full bg-yellow-400"></span> Medium
                    </span>
                    <span class="inline-flex items-center gap-1">
                        <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span> Low
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                @foreach($cards as $key => $sty)
                    @php $st = $prio[$key] ?? null; @endphp
                    <div class="rounded-xl border {{ $sty['ring'] }} {{ $sty['bg'] }} p-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                @if($sty['icon']==='exclamation-triangle')
                                    <x-heroicon-o-exclamation-triangle class="h-5 w-5 {{ $sty['iconClass'] }}" />
                                @elseif($sty['icon']==='clock')
                                    <x-heroicon-o-clock class="h-5 w-5 {{ $sty['iconClass'] }}" />
                                @else
                                    <x-heroicon-o-check-circle class="h-5 w-5 {{ $sty['iconClass'] }}" />
                                @endif
                                <span class="font-semibold text-gray-900">{{ $sty['label'] }}</span>
                            </div>
                            {!! $badge($st['grade'] ?? '—', 'bg-white/70', 'text-gray-900') !!}
                        </div>

                        <div class="mt-3 grid grid-cols-3 gap-2 text-sm">
                            <div class="bg-white/70 rounded-lg p-2 text-gray-800">
                                <div class="flex items-center gap-1">
                                    <x-heroicon-o-presentation-chart-line class="h-4 w-4" />
                                    <span>Avg</span>
                                </div>
                                <div class="font-semibold">{{ is_null($st['avg_hours'] ?? null) ? '—' : number_format($st['avg_hours'],2) }} j</div>
                            </div>
                            <div class="bg-white/70 rounded-lg p-2 text-gray-800">
                                <div class="flex items-center gap-1">
                                    <x-heroicon-o-adjustments-vertical class="h-4 w-4" />
                                    <span>Median</span>
                                </div>
                                <div class="font-semibold">{{ is_null($st['median_hours'] ?? null) ? '—' : number_format($st['median_hours'],2) }} j</div>
                            </div>
                            <div class="bg-white/70 rounded-lg p-2 text-gray-800">
                                <div class="flex items-center gap-1">
                                    <x-heroicon-o-arrow-trending-up class="h-4 w-4" />
                                    <span>P90</span>
                                </div>
                                <div class="font-semibold">{{ is_null($st['p90_hours'] ?? null) ? '—' : number_format($st['p90_hours'],2) }} j</div>
                            </div>
                        </div>

                        <div class="mt-3 flex items-center justify-between text-sm">
                            <div class="text-gray-700">
                                SLA: <span class="font-semibold">{{ is_null($st['sla_hours'] ?? null) ? 'n/a' : number_format($st['sla_hours'],0).' j' }}</span>
                            </div>
                            <div class="flex items-center gap-1 text-gray-700">
                                <x-heroicon-o-check-circle class="h-4 w-4" />
                                Tepat SLA: <span class="font-semibold">{{ is_null($st['sla_hit_rate'] ?? null) ? 'n/a' : number_format($st['sla_hit_rate'],0).'%' }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <p class="text-xs text-gray-500 mt-3">
                Penilaian: <span class="font-semibold">Cepat</span> (≥90% tepat SLA), <span class="font-semibold">Sedang</span> (70–89%), <span class="font-semibold">Perlu Perbaikan</span> (&lt;70%).
            </p>
        </section>

        {{-- Charts --}}
        <section class="grid grid-cols-1 2xl:grid-cols-2 gap-6">
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                <h3 class="text-base font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <x-heroicon-o-chart-bar class="h-5 w-5 text-gray-900" />
                    Tren Bulanan – {{ $year }}
                </h3>
                <div wire:ignore>
                    <canvas id="monthlyChart" wire:key="monthly-{{ $year }}" class="w-full" style="max-height:420px"></canvas>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                <h3 class="text-base font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <x-heroicon-o-presentation-chart-line class="h-5 w-5 text-gray-900" />
                    Total Tahunan – {{ $yearsBack }} Tahun
                </h3>
                <div wire:ignore>
                    <canvas id="yearlyChart" wire:key="yearly-{{ $year }}" class="w-full" style="max-height:420px"></canvas>
                </div>
            </div>
        </section>

        {{-- Data Table --}}
        <section class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
            <h3 class="text-base font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <x-heroicon-o-bars-3 class="h-5 w-5 text-gray-900" />
                Angka Bulanan
            </h3>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-600">
                            <th class="py-2 pr-4">Bulan</th>
                            <th class="py-2 pr-4">Room</th>
                            <th class="py-2 pr-4">Vehicle</th>
                            <th class="py-2 pr-4">Ticket</th>
                            <th class="py-2 pr-4">Guestbook</th>
                            <th class="py-2 pr-4">Delivery</th>
                            <th class="py-2 pr-4">Total</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-800">
                        @foreach($monthly['labels'] as $i => $m)
                            <tr class="border-t">
                                <td class="py-2 pr-4">{{ $m }}</td>
                                <td class="py-2 pr-4">{{ $monthly['room'][$i] }}</td>
                                <td class="py-2 pr-4">{{ $monthly['vehicle'][$i] }}</td>
                                <td class="py-2 pr-4">{{ $monthly['ticket'][$i] }}</td>
                                <td class="py-2 pr-4">{{ $monthly['guestbook'][$i] }}</td>
                                <td class="py-2 pr-4">{{ $monthly['delivery'][$i] }}</td>
                                <td class="py-2 pr-4">
                                    {{ $monthly['room'][$i] + $monthly['vehicle'][$i] + $monthly['ticket'][$i] + $monthly['guestbook'][$i] + $monthly['delivery'][$i] }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

    </main>

    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    <script>
        let __monthlyChart, __yearlyChart;

        function rebuildCharts(monthly, yearly) {
            if (__monthlyChart) __monthlyChart.destroy();
            if (__yearlyChart) __yearlyChart.destroy();

            const paletteLine = {
                room: '#1d4ed8',
                vehicle: '#059669',
                ticket: '#dc2626',
                guestbook: '#7c3aed',
                delivery: '#f59e0b',
            };
            const paletteFill = { ...paletteLine };

            // Monthly
            const mctx = document.getElementById('monthlyChart')?.getContext('2d');
            if (mctx) {
                const keys = ['room','vehicle','ticket','guestbook','delivery'];
                const mDatasets = keys.filter(k => Array.isArray(monthly[k])).map(k => ({
                    label: k.charAt(0).toUpperCase() + k.slice(1),
                    data: monthly[k],
                    borderColor: paletteLine[k],
                    tension: .35,
                    pointRadius: 3,
                    fill: false
                }));
                __monthlyChart = new Chart(mctx, {
                    type: 'line',
                    data: { labels: monthly.labels, datasets: mDatasets },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        plugins: { legend: { position: 'top' } },
                        scales: { y: { beginAtZero: true, ticks: { precision: 0, stepSize: 1 } }, x: { grid: { display: false } } }
                    }
                });
            }

            // Yearly
            const yctx = document.getElementById('yearlyChart')?.getContext('2d');
            if (yctx) {
                const keys = ['room','vehicle','ticket','guestbook','delivery'];
                const yDatasets = keys.filter(k => Array.isArray(yearly[k])).map(k => ({
                    label: k.charAt(0).toUpperCase() + k.slice(1),
                    data: yearly[k],
                    backgroundColor: paletteFill[k],
                }));
                __yearlyChart = new Chart(yctx, {
                    type: 'bar',
                    data: { labels: yearly.labels, datasets: yDatasets },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        plugins: { legend: { position: 'top' } },
                        scales: { y: { beginAtZero: true, ticks: { precision: 0, stepSize: 1 } } }
                    }
                });
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            rebuildCharts(@json($monthly), @json($yearly));
        });

        document.addEventListener('report-data-updated', (e) => {
            const { monthly, yearly } = e.detail || {};
            if (monthly && yearly) rebuildCharts(monthly, yearly);
        });

        function canvasToDataURL(id) {
            const c = document.getElementById(id);
            if (!c) return null;
            try { return c.toDataURL('image/png', 1.0); } catch (e) { return null; }
        }

        const dlBtn = document.getElementById('downloadPdfBtn');
        const dlOverlay = document.getElementById('downloadOverlay');
        const btnSpinner = document.getElementById('btnSpinner');
        const btnLabel = document.getElementById('btnLabel');

        function setDownloading(state) {
            if (!dlBtn) return;
            if (state) {
                dlBtn.disabled = true;
                btnSpinner?.classList.remove('hidden');
                btnLabel && (btnLabel.textContent = 'Menyiapkan…');
                dlOverlay?.classList.remove('hidden');
            } else {
                dlBtn.disabled = false;
                btnSpinner?.classList.add('hidden');
                btnLabel && (btnLabel.textContent = 'Download PDF');
                dlOverlay?.classList.add('hidden');
            }
        }
        document.getElementById('hideOverlay')?.addEventListener('click', () => setDownloading(false));

        dlBtn?.addEventListener('click', async () => {
            const monthly_img = canvasToDataURL('monthlyChart');
            const yearly_img  = canvasToDataURL('yearlyChart');

            setDownloading(true);
            try {
                await @this.call('exportPdf', { monthly_img, yearly_img });
            } catch (e) {
                console.error(e);
            } finally {
                setTimeout(() => setDownloading(false), 1200);
            }
        });

        window.addEventListener('livewire:error', () => setDownloading(false));
    </script>
</div>
