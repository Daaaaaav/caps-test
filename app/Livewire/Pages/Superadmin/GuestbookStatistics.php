<?php

namespace App\Livewire\Pages\Superadmin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use App\Models\Guestbook;

#[Layout('layouts.superadmin')]
#[Title('Guestbook Statistics')]
class GuestbookStatistics extends Component
{
    public $timeRange = '90days';
    public $showList  = false;

    public function setTimeRange($range): void
    {
        $this->timeRange = $range;
    }

    public function toggleList(): void
    {
        $this->showList = !$this->showList;
    }

    public function render()
    {
        try {
            $companyId = Auth::user()->company_id;

            $days = match($this->timeRange) {
                '30days' => 30,
                '90days' => 90,
                default  => 7,
            };

            $since = now()->subDays($days)->startOfDay();

            // ── KPI counts ────────────────────────────────────────────────────
            $totalVisitors = Guestbook::where('company_id', $companyId)
                ->where('created_at', '>=', $since)
                ->count();

            // Currently inside: checked in but not yet checked out
            $checkedIn = Guestbook::where('company_id', $companyId)
                ->where('created_at', '>=', $since)
                ->whereNotNull('jam_in')
                ->whereNull('jam_out')
                ->count();

            // Already left: has a check-out time
            $checkedOut = Guestbook::where('company_id', $companyId)
                ->where('created_at', '>=', $since)
                ->whereNotNull('jam_out')
                ->count();

            // ── Daily chart — zero-filled for every day in range ──────────────
            $raw = Guestbook::where('company_id', $companyId)
                ->where('created_at', '>=', $since)
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->groupByRaw('DATE(created_at)')
                ->orderByRaw('DATE(created_at)')
                ->pluck('count', 'date');

            $labels = [];
            $data   = [];
            for ($i = $days - 1; $i >= 0; $i--) {
                $date     = now()->subDays($i)->format('Y-m-d');
                $labels[] = now()->subDays($i)->format('M d');
                $data[]   = (int) ($raw[$date] ?? 0);
            }

            // ── Visitor list ──────────────────────────────────────────────────
            $guestbooks = $this->showList
                ? Guestbook::where('company_id', $companyId)
                    ->where('created_at', '>=', $since)
                    ->orderBy('created_at', 'desc')
                    ->get()
                : collect();

            $stats = [
                ['label' => 'Total Visitors', 'value' => $totalVisitors,                                                    'color' => 'blue'],
                ['label' => 'Currently In',   'value' => $checkedIn,                                                        'color' => 'yellow'],
                ['label' => 'Checked Out',    'value' => $checkedOut,                                                       'color' => 'green'],
                ['label' => 'Avg per Day',    'value' => $days > 0 ? round($totalVisitors / $days, 1) : 0,                  'color' => 'purple'],
            ];

            $this->dispatch('guestbook-chart-updated', labels: $labels, data: $data);

            return view('livewire.pages.superadmin.guestbook-statistics', [
                'stats'      => $stats,
                'labels'     => $labels,
                'data'       => $data,
                'guestbooks' => $guestbooks,
            ]);

        } catch (\Exception $e) {
            $this->dispatch('toast',
                type: 'error', title: 'Error',
                message: 'Failed to retrieve guestbook data: ' . $e->getMessage(),
                duration: 4000
            );

            return view('livewire.pages.superadmin.guestbook-statistics', [
                'stats' => [
                    ['label' => 'Total Visitors', 'value' => 0, 'color' => 'blue'],
                    ['label' => 'Currently In',   'value' => 0, 'color' => 'yellow'],
                    ['label' => 'Checked Out',    'value' => 0, 'color' => 'green'],
                    ['label' => 'Avg per Day',    'value' => 0, 'color' => 'purple'],
                ],
                'labels'     => [],
                'data'       => [],
                'guestbooks' => collect(),
            ]);
        }
    }
}
