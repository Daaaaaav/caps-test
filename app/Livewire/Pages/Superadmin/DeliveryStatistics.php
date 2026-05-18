<?php

namespace App\Livewire\Pages\Superadmin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use App\Models\Delivery;

#[Layout('layouts.superadmin')]
#[Title('Delivery Statistics')]
class DeliveryStatistics extends Component
{
    public $timeRange = '7days';
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
            // Delivery statuses: pending | stored | done
            // "done" covers both delivered and taken (direction field distinguishes them)
            $totalDeliveries     = Delivery::where('company_id', $companyId)->where('created_at', '>=', $since)->count();
            $pendingDeliveries   = Delivery::where('company_id', $companyId)->where('created_at', '>=', $since)->where('status', 'pending')->count();
            $storedDeliveries    = Delivery::where('company_id', $companyId)->where('created_at', '>=', $since)->where('status', 'stored')->count();
            $completedDeliveries = Delivery::where('company_id', $companyId)->where('created_at', '>=', $since)->where('status', 'done')->count();

            // ── Daily chart — zero-filled for every day in range ──────────────
            $raw = Delivery::where('company_id', $companyId)
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

            // ── Delivery list ─────────────────────────────────────────────────
            $deliveries = $this->showList
                ? Delivery::where('company_id', $companyId)
                    ->where('created_at', '>=', $since)
                    ->orderBy('created_at', 'desc')
                    ->get()
                : collect();

            $stats = [
                ['label' => 'Total Deliveries', 'value' => $totalDeliveries,     'color' => 'blue'],
                ['label' => 'Pending',           'value' => $pendingDeliveries,   'color' => 'yellow'],
                ['label' => 'Stored',            'value' => $storedDeliveries,    'color' => 'purple'],
                ['label' => 'Completed',         'value' => $completedDeliveries, 'color' => 'green'],
            ];

            return view('livewire.pages.superadmin.delivery-statistics', [
                'stats'      => $stats,
                'labels'     => $labels,
                'data'       => $data,
                'deliveries' => $deliveries,
            ]);

        } catch (\Exception $e) {
            $this->dispatch('toast',
                type: 'error', title: 'Error',
                message: 'Failed to retrieve delivery data: ' . $e->getMessage(),
                duration: 4000
            );

            return view('livewire.pages.superadmin.delivery-statistics', [
                'stats' => [
                    ['label' => 'Total Deliveries', 'value' => 0, 'color' => 'blue'],
                    ['label' => 'Pending',           'value' => 0, 'color' => 'yellow'],
                    ['label' => 'Stored',            'value' => 0, 'color' => 'purple'],
                    ['label' => 'Completed',         'value' => 0, 'color' => 'green'],
                ],
                'labels'     => [],
                'data'       => [],
                'deliveries' => collect(),
            ]);
        }
    }
}
