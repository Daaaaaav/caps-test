<?php

namespace App\Livewire\Pages\Superadmin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use App\Models\BookingRoom;
use App\Models\VehicleBooking;
use App\Models\Ticket;
use App\Models\Guestbook;
use App\Models\Delivery;
use App\Models\Company;
use App\Models\User;

#[Layout('layouts.superadmin')]
#[Title('Reports & Evaluation')]
class Report extends Component
{
    public int $year;
    public int $yearsBack = 5;

    public array $monthly = [];
    public array $yearly = [];
    public array $summary = [];
    public array $analysis = [];

    // Performa tiket (SLA)
    public array $ticketPerf = [];

    // Target SLA (jam)
    public array $slaTargets = [
        'high' => 24,
        'medium' => 48,
        'low' => 72,
    ];

    public ?int $companyId = null;
    public ?array $company = null;

    public function mount(): void
    {
        $this->companyId = Auth::user()->company_id ?? null;

        // Company header data
        $co = $this->companyId ? Company::find($this->companyId) : null;
        $imageUrl = null;
        if ($co && $co->image) {
            $imageUrl = str_starts_with($co->image, 'http')
                ? $co->image
                : (Storage::disk(config('filesystems.default'))->exists($co->image)
                    ? Storage::url($co->image)
                    : $co->image);
        }
        $this->company = [
            'company_name' => $co->company_name ?? '—',
            'image' => $imageUrl,
        ];

        $this->year = (int) now()->year;
        $this->buildData();

        $this->dispatch('report-data-updated', monthly: $this->monthly, yearly: $this->yearly);
    }

    public function updatedYear(): void
    {
        $this->buildData();
        $this->dispatch('report-data-updated', monthly: $this->monthly, yearly: $this->yearly);
    }

    /** Scope queries by company_id jika kolomnya ada */
    private function scoped($query)
    {
        if ($this->companyId) {
            $table = $query->getModel()->getTable();
            if (Schema::hasColumn($table, 'company_id')) {
                $query->where($table . '.company_id', $this->companyId);
            }
        }
        return $query;
    }

    /** -------- MAIN DATA BUILDER -------- */
    private function buildData(): void
    {
        $months = range(1, 12);
        $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        // Monthly
        $room = $this->scoped(BookingRoom::query())
            ->selectRaw('MONTH(created_at) m, COUNT(*) c')
            ->whereYear('created_at', $this->year)
            ->groupBy('m')->pluck('c', 'm')->toArray();

        $veh = $this->scoped(VehicleBooking::query())
            ->selectRaw('MONTH(created_at) m, COUNT(*) c')
            ->whereYear('created_at', $this->year)
            ->groupBy('m')->pluck('c', 'm')->toArray();

        $tick = $this->scoped(Ticket::query())
            ->selectRaw('MONTH(created_at) m, COUNT(*) c')
            ->whereYear('created_at', $this->year)
            ->groupBy('m')->pluck('c', 'm')->toArray();

        $gb = $this->scoped(Guestbook::query())
            ->selectRaw('MONTH(created_at) m, COUNT(*) c')
            ->whereYear('created_at', $this->year)
            ->groupBy('m')->pluck('c', 'm')->toArray();

        $del = $this->scoped(Delivery::query())
            ->selectRaw('MONTH(created_at) m, COUNT(*) c')
            ->whereYear('created_at', $this->year)
            ->groupBy('m')->pluck('c', 'm')->toArray();

        $roomArr = array_map(fn($i) => (int) ($room[$i] ?? 0), $months);
        $vehArr = array_map(fn($i) => (int) ($veh[$i] ?? 0), $months);
        $tickArr = array_map(fn($i) => (int) ($tick[$i] ?? 0), $months);
        $gbArr = array_map(fn($i) => (int) ($gb[$i] ?? 0), $months);
        $delArr = array_map(fn($i) => (int) ($del[$i] ?? 0), $months);

        $this->monthly = [
            'labels' => $labels,
            'room' => $roomArr,
            'vehicle' => $vehArr,
            'ticket' => $tickArr,
            'guestbook' => $gbArr,
            'delivery' => $delArr,
        ];

        // Yearly
        $startYear = $this->year - ($this->yearsBack - 1);
        $years = range($startYear, $this->year);
        $startDate = Carbon::create($startYear, 1, 1, 0, 0, 0);
        $endDate = Carbon::create($this->year, 12, 31, 23, 59, 59);

        $yrRoom = $this->scoped(BookingRoom::query())
            ->selectRaw('YEAR(created_at) y, COUNT(*) c')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('y')->pluck('c', 'y')->toArray();

        $yrVeh = $this->scoped(VehicleBooking::query())
            ->selectRaw('YEAR(created_at) y, COUNT(*) c')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('y')->pluck('c', 'y')->toArray();

        $yrTick = $this->scoped(Ticket::query())
            ->selectRaw('YEAR(created_at) y, COUNT(*) c')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('y')->pluck('c', 'y')->toArray();

        $yrGb = $this->scoped(Guestbook::query())
            ->selectRaw('YEAR(created_at) y, COUNT(*) c')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('y')->pluck('c', 'y')->toArray();

        $yrDel = $this->scoped(Delivery::query())
            ->selectRaw('YEAR(created_at) y, COUNT(*) c')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('y')->pluck('c', 'y')->toArray();

        $this->yearly = [
            'labels' => array_map(fn($y) => (string) $y, $years),
            'room' => array_map(fn($y) => (int) ($yrRoom[$y] ?? 0), $years),
            'vehicle' => array_map(fn($y) => (int) ($yrVeh[$y] ?? 0), $years),
            'ticket' => array_map(fn($y) => (int) ($yrTick[$y] ?? 0), $years),
            'guestbook' => array_map(fn($y) => (int) ($yrGb[$y] ?? 0), $years),
            'delivery' => array_map(fn($y) => (int) ($yrDel[$y] ?? 0), $years),
        ];

        // Summary + analysis
        $totRoom = array_sum($roomArr);
        $totVeh = array_sum($vehArr);
        $totTick = array_sum($tickArr);
        $totGb = array_sum($gbArr);
        $totDel = array_sum($delArr);
        $totAll = $totRoom + $totVeh + $totTick + $totGb + $totDel;

        $idx = range(0, 11);
        $totalsPerMonth = array_map(fn($i) => $roomArr[$i] + $vehArr[$i] + $tickArr[$i] + $gbArr[$i] + $delArr[$i], $idx);
        $bestIndex = $this->maxIndex($totalsPerMonth);
        $worstIndex = $this->minIndex($totalsPerMonth);

        $prevYear = $this->year - 1;
        $prevTotals = [
            'room' => $this->scoped(BookingRoom::query())->whereYear('created_at', $prevYear)->count(),
            'vehicle' => $this->scoped(VehicleBooking::query())->whereYear('created_at', $prevYear)->count(),
            'ticket' => $this->scoped(Ticket::query())->whereYear('created_at', $prevYear)->count(),
            'guestbook' => $this->scoped(Guestbook::query())->whereYear('created_at', $prevYear)->count(),
            'delivery' => $this->scoped(Delivery::query())->whereYear('created_at', $prevYear)->count(),
        ];

        $growthYoY = [
            'room' => $this->pct($totRoom, $prevTotals['room']),
            'vehicle' => $this->pct($totVeh, $prevTotals['vehicle']),
            'ticket' => $this->pct($totTick, $prevTotals['ticket']),
            'guestbook' => $this->pct($totGb, $prevTotals['guestbook']),
            'delivery' => $this->pct($totDel, $prevTotals['delivery']),
        ];
        $growthYoY['overall'] = $this->pct($totAll, array_sum($prevTotals));

        $this->summary = [
            'selected_year' => $this->year,
            'total_activity' => $totAll,
            'busiest_month' => $labels[$bestIndex] ?? '-',
            'busiest_total' => $totalsPerMonth[$bestIndex] ?? 0,
            'quietest_month' => $labels[$worstIndex] ?? '-',
            'quietest_total' => $totalsPerMonth[$worstIndex] ?? 0,
            'growth_yoy' => $growthYoY,
        ];

        $momOverall = $this->momSeries($totalsPerMonth);
        $this->analysis = [
            'kpi' => [
                'total_room' => $totRoom,
                'total_vehicle' => $totVeh,
                'total_ticket' => $totTick,
                'total_guestbook' => $totGb,
                'total_delivery' => $totDel,
                'overall_total' => $totAll,
                'avg_per_month' => round($totAll / 12, 2),
                'growth_yoy' => $growthYoY,
            ],
            'peaks' => [
                'best' => ['label' => $labels[$bestIndex] ?? '-', 'value' => $totalsPerMonth[$bestIndex] ?? 0],
                'worst' => ['label' => $labels[$worstIndex] ?? '-', 'value' => $totalsPerMonth[$worstIndex] ?? 0],
            ],
            'mom' => [
                'overall' => $momOverall,
                'room' => $this->momSeries($roomArr),
                'vehicle' => $this->momSeries($vehArr),
                'ticket' => $this->momSeries($tickArr),
                'guestbook' => $this->momSeries($gbArr),
                'delivery' => $this->momSeries($delArr),
            ],
            'volatility' => [
                'room_cv' => round($this->coefVar($roomArr), 2),
                'vehicle_cv' => round($this->coefVar($vehArr), 2),
                'ticket_cv' => round($this->coefVar($tickArr), 2),
                'guestbook_cv' => round($this->coefVar($gbArr), 2),
                'delivery_cv' => round($this->coefVar($delArr), 2),
            ],
            'moving_avg_3' => $this->movingAverage($totalsPerMonth, 3),
            'recommendations' => $this->buildRecs($growthYoY, $totalsPerMonth, $labels),
        ];

        // -------- Performa tiket (SLA) --------
        $this->ticketPerf = $this->buildTicketPerf();
    }

    /** Performa tiket: prioritas & admin (assignment terakhir via ticket_assignments) */
    private function buildTicketPerf(): array
    {
        $ticketsTable = (new Ticket)->getTable();   // 'tickets'
        $usersTable = (new User)->getTable();     // 'users'
        $assignTable = 'ticket_assignments';

        // Ambil tiket RESOLVED/CLOSED pada tahun terpilih
        $ticketQuery = DB::table($ticketsTable)
            ->select('ticket_id', 'priority', 'created_at', 'updated_at')
            ->whereYear('created_at', $this->year)
            ->whereIn('status', ['RESOLVED', 'CLOSED']);

        if (Schema::hasColumn($ticketsTable, 'deleted_at')) {
            $ticketQuery->whereNull("$ticketsTable.deleted_at");
        }
        if ($this->companyId && Schema::hasColumn($ticketsTable, 'company_id')) {
            $ticketQuery->where("$ticketsTable.company_id", $this->companyId);
        }

        $tickets = $ticketQuery->get();

        if ($tickets->isEmpty()) {
            return ['by_priority' => [], 'by_admin' => [], 'verdicts' => []];
        }

        // Assignment terakhir per ticket (pakai assignment_id terbesar)
        $ticketIds = $tickets->pluck('ticket_id')->unique()->values()->all();

        $assignments = DB::table($assignTable)
            ->select('assignment_id', 'ticket_id', 'user_id')
            ->whereIn('ticket_id', $ticketIds)
            ->orderByDesc('assignment_id');

        if (Schema::hasColumn($assignTable, 'deleted_at')) {
            $assignments->whereNull('deleted_at');
        }

        $assignments = $assignments->get();

        $latestAssigneeByTicket = [];
        foreach ($assignments as $a) {
            if (!isset($latestAssigneeByTicket[$a->ticket_id])) {
                $latestAssigneeByTicket[$a->ticket_id] = (int) $a->user_id;
            }
        }

        // Map user
        $assigneeIds = array_values(array_unique(array_filter($latestAssigneeByTicket)));
        $userPk = Schema::hasColumn($usersTable, 'user_id') ? 'user_id' : 'id';

        $userQ = DB::table($usersTable)->select($userPk . ' as id', 'full_name', 'email');
        if (!empty($assigneeIds))
            $userQ->whereIn($userPk, $assigneeIds);
        else
            $userQ->whereRaw('1=0');
        if ($this->companyId && Schema::hasColumn($usersTable, 'company_id')) {
            $userQ->where('company_id', $this->companyId);
        }
        $users = $userQ->get()->keyBy('id');

        $nameOf = function (?int $id) use ($users) {
            if (!$id)
                return 'Tidak Ditugaskan';
            $u = $users->get($id);
            return $u->full_name ?? ('User #' . $id);
        };

        // helper durasi dalam jam
        $hoursBetween = function ($a, $b) {
            if (!$a || !$b)
                return null;
            $start = $a instanceof Carbon ? $a : Carbon::parse($a);
            $end = $b instanceof Carbon ? $b : Carbon::parse($b);
            if ($end->lessThan($start))
                return null;
            return round($start->diffInMinutes($end) / 60, 2);
        };

        // ----- Rekap PRIORITAS -----
        $byPriority = [];
        foreach ($tickets as $t) {
            $p = strtolower($t->priority ?? 'unspecified'); // low|medium|high
            $p = in_array($p, ['low', 'medium', 'high']) ? $p : 'unspecified';

            $h = $hoursBetween($t->created_at, $t->updated_at);
            if ($h === null)
                continue;

            $byPriority[$p]['samples'][] = $h;
        }

        $prioOut = [];
        foreach ($byPriority as $p => $arr) {
            $samples = $arr['samples'] ?? [];
            sort($samples);
            $count = count($samples);
            $avg = $count ? round(array_sum($samples) / $count, 2) : null;
            $med = $this->median($samples);
            $p90 = $this->percentile($samples, 90);

            $sla = $this->slaTargets[$p] ?? null;

            // Hit rate SLA
            $hit = null;
            if ($sla && $count) {
                $hits = count(array_filter($samples, fn($h) => $h <= $sla));
                $hit = round(($hits / $count) * 100, 2);
            }

            // --- Grade: FIXED (tidak akan "—" saat ada data) ---
            $grade = null;
            if ($count) {
                if (!is_null($hit)) {
                    $grade = $hit >= 90 ? 'Cepat' : ($hit >= 70 ? 'Sedang' : 'Perlu Perbaikan');
                } else {
                    // fallback jika tidak ada target SLA (prioritas 'unspecified'):
                    // gunakan rata-rata jam untuk menilai
                    $thresholdFast = 48;  // <=48 jam cepat
                    $thresholdOkay = 72;  // 49–72 jam sedang
                    $grade = $avg <= $thresholdFast ? 'Cepat' : ($avg <= $thresholdOkay ? 'Sedang' : 'Perlu Perbaikan');
                }
            }

            $prioOut[$p] = [
                'count' => $count,
                'avg_hours' => $avg,
                'median_hours' => $med,
                'p90_hours' => $p90,
                'sla_hours' => $sla,
                'sla_hit_rate' => $hit, // %
                'grade' => $grade,
            ];
        }

        // ----- Rekap ADMIN (agent) -----
        $byAdmin = [];
        foreach ($tickets as $t) {
            $adminId = $latestAssigneeByTicket[$t->ticket_id] ?? 0;
            $h = $hoursBetween($t->created_at, $t->updated_at);
            if ($h === null)
                continue;

            $p = strtolower($t->priority ?? 'unspecified');
            $p = in_array($p, ['low', 'medium', 'high']) ? $p : 'unspecified';

            $byAdmin[$adminId]['name'] = $nameOf($adminId);
            $byAdmin[$adminId]['samples'][] = $h;
            $byAdmin[$adminId]['by_priority'][$p]['samples'][] = $h;
        }

        $adminOut = [];
        foreach ($byAdmin as $adminId => $data) {
            $samples = $data['samples'] ?? [];
            sort($samples);
            $count = count($samples);

            $overall = [
                'count' => $count,
                'avg_hours' => $count ? round(array_sum($samples) / $count, 2) : null,
                'median_hours' => $this->median($samples),
                'p90_hours' => $this->percentile($samples, 90),
            ];

            $prioStats = [];
            foreach (($data['by_priority'] ?? []) as $p => $arr) {
                $s = $arr['samples'] ?? [];
                sort($s);
                $c = count($s);
                $sla = $this->slaTargets[$p] ?? null;
                $hit = ($sla && $c) ? round((count(array_filter($s, fn($h) => $h <= $sla)) / $c) * 100, 2) : null;

                $prioStats[$p] = [
                    'count' => $c,
                    'avg_hours' => $c ? round(array_sum($s) / $c, 2) : null,
                    'median_hours' => $this->median($s),
                    'p90_hours' => $this->percentile($s, 90),
                    'sla_hours' => $sla,
                    'sla_hit_rate' => $hit,
                ];
            }

            $adminOut[] = [
                'admin_id' => $adminId,
                'admin_name' => $data['name'] ?? ('User #' . $adminId),
                'overall' => $overall,
                'by_priority' => $prioStats,
            ];
        }

        // Urutkan admin terbaik (rata-rata jam terendah)
        usort($adminOut, function ($a, $b) {
            $aa = $a['overall']['avg_hours'] ?? PHP_FLOAT_MAX;
            $bb = $b['overall']['avg_hours'] ?? PHP_FLOAT_MAX;
            return $aa <=> $bb;
        });

        // Verdict sederhana per prioritas
        $verdicts = [];
        foreach ($prioOut as $p => $st) {
            $hit = $st['sla_hit_rate'] ?? null;
            $g = $st['grade'] ?? null;
            $label = strtoupper($p);
            if ($g === 'Cepat')
                $verdicts[] = "$label: ✅ Cepat (Tepat SLA " . number_format($hit ?? 0, 0) . "%)";
            elseif ($g === 'Sedang')
                $verdicts[] = "$label: ⚠️ Sedang (Tepat SLA " . number_format($hit ?? 0, 0) . "%)";
            elseif ($g === 'Perlu Perbaikan')
                $verdicts[] = "$label: ❌ Perlu Perbaikan (Tepat SLA " . number_format($hit ?? 0, 0) . "%)";
        }

        return [
            'by_priority' => $prioOut,
            'by_admin' => $adminOut,
            'verdicts' => $verdicts,
        ];
    }

    private function median(array $sorted): ?float
    {
        $n = count($sorted);
        if (!$n)
            return null;
        $mid = intdiv($n, 2);
        if ($n % 2)
            return round($sorted[$mid], 2);
        return round(($sorted[$mid - 1] + $sorted[$mid]) / 2, 2);
    }

    private function percentile(array $sorted, int $p): ?float
    {
        $n = count($sorted);
        if (!$n)
            return null;
        if ($p <= 0)
            return round($sorted[0], 2);
        if ($p >= 100)
            return round($sorted[$n - 1], 2);
        $rank = ($p / 100) * ($n - 1);
        $l = (int) floor($rank);
        $u = (int) ceil($rank);
        if ($l === $u)
            return round($sorted[$l], 2);
        $w = $rank - $l;
        return round($sorted[$l] * (1 - $w) + $sorted[$u] * $w, 2);
    }

    private function buildRecs(array $growthYoY, array $totalsPerMonth, array $labels): array
    {
        $recs = [];
        if (($growthYoY['ticket'] ?? 0) > 10) {
            $recs[] = 'Volume tiket naik >10% YoY — pertimbangkan tambah kapasitas support atau otomasi triase.';
        }
        if (($growthYoY['delivery'] ?? 0) > 10) {
            $recs[] = 'Delivery meningkat — periksa kapasitas storage dan SOP serah-terima.';
        }
        $bestIndex = $this->maxIndex($totalsPerMonth);
        if ($bestIndex !== null) {
            $recs[] = 'Bulan puncak ' . ($labels[$bestIndex] ?? '') . ' — rencanakan SDM & aset sejak awal periode.';
        }

        // Insight dari SLA
        $prio = $this->ticketPerf['by_priority'] ?? [];
        foreach (['high', 'medium', 'low'] as $p) {
            $hit = $prio[$p]['sla_hit_rate'] ?? null;
            if (!is_null($hit) && $hit < 80) {
                $recs[] = strtoupper($p) . ' tepat SLA <80% — cek eskalasi, antrian, dan jadwal shift.';
            }
        }

        if (empty($recs)) {
            $recs[] = 'Pemakaian stabil — pertahankan kapasitas saat ini sambil pantau tren bulanan.';
        }
        return $recs;
    }

    // ---------- Helpers umum ----------
    private function pct(int|float $cur, int|float $prev): ?float
    {
        if ($prev <= 0)
            return null;
        return round((($cur - $prev) / $prev) * 100, 2);
    }

    private function momSeries(array $values): array
    {
        $out = [];
        for ($i = 1; $i < count($values); $i++) {
            $prev = $values[$i - 1];
            $cur = $values[$i];
            $out[] = $prev > 0 ? round((($cur - $prev) / $prev) * 100, 2) : null;
        }
        return $out;
    }

    private function movingAverage(array $values, int $window): array
    {
        $n = count($values);
        $out = [];
        for ($i = 0; $i < $n; $i++) {
            if ($i + 1 < $window) {
                $out[] = null;
                continue;
            }
            $slice = array_slice($values, $i + 1 - $window, $window);
            $out[] = round(array_sum($slice) / $window, 2);
        }
        return $out;
    }

    private function coefVar(array $values): float
    {
        $n = count($values);
        if ($n === 0)
            return 0.0;
        $mean = array_sum($values) / $n ?: 0.0;
        if ($mean == 0.0)
            return 0.0;
        $var = 0.0;
        foreach ($values as $v)
            $var += ($v - $mean) ** 2;
        $std = sqrt($var / $n);
        return $std / $mean;
    }

    private function maxIndex(array $values): ?int
    {
        if (!$values)
            return null;
        $max = max($values);
        return array_search($max, $values, true);
    }

    private function minIndex(array $values): ?int
    {
        if (!$values)
            return null;
        $min = min($values);
        return array_search($min, $values, true);
    }

    public function companyLogoPath($companyName)
    {
        $parts = preg_split('/\s+/', trim($companyName));
        $location = strtolower(end($parts));
        $filename = "kebun-raya-" . $location . ".png";
        return public_path("images/logo/" . $filename);
    }

    // PDF
    public function exportPdf(array $payload = [])
    {
        $monthlyImg = $payload['monthly_img'] ?? null;
        $yearlyImg = $payload['yearly_img'] ?? null;

        $data = [
            'year' => $this->year,
            'monthly' => $this->monthly,
            'yearly' => $this->yearly,
            'summary' => $this->summary,
            'analysis' => $this->analysis,
            'ticket_perf' => $this->ticketPerf,
            'company' => $this->company,
            'company_logo' => $this->companyLogoPath($this->company['company_name']),
            'img' => [
                'monthly' => $monthlyImg,
                'yearly' => $yearlyImg,
            ],
            'generated_by' => Auth::user()->full_name ?? 'System',
            'generated_at' => now()->format('d M Y H:i'),
        ];

        // ✨ Use the same path as the old function
        $pdf = app('dompdf.wrapper')->setPaper('a4', 'portrait');

        // ✨ Same SSL bypass context (required for your images)
        $pdf->getDomPDF()->setHttpContext(stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ]));

        // ✨ Load the same Blade view as before
        $pdf->loadView('pdf.report-pdf', $data);

        // ✨ Download exactly like the new function
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'Report-' . $this->year . '.pdf');
    }

    public function render()
    {
        return view('livewire.pages.superadmin.report', [
            'year' => $this->year,
            'yearsBack' => $this->yearsBack,
            'monthly' => $this->monthly,
            'yearly' => $this->yearly,
            'summary' => $this->summary,
            'company' => $this->company,
            'ticketPerf' => $this->ticketPerf, // used by web snapshot (colored + icons)
        ]);
    }
}
