<?php

namespace App\Livewire\Pages\Superadmin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Delivery;

#[Layout('layouts.superadmin')]
#[Title('Delivery Statistics')]
class DeliveryStatistics extends Component
{
    public $timeRange = '7days';
    public $showList = false;

    public function setTimeRange($range)
    {
        $this->timeRange = $range;
    }

    public function toggleList()
    {
        $this->showList = !$this->showList;
    }

    public function render()
    {
        $companyId = Auth::user()->company_id;

        // Determine date range
        $days = match($this->timeRange) {
            '7days' => 7,
            '30days' => 30,
            '90days' => 90,
            default => 7,
        };

        // Get delivery stats
        $totalDeliveries = Delivery::where('company_id', $companyId)
            ->where('created_at', '>=', now()->subDays($days))
            ->count();

        $pendingDeliveries = Delivery::where('company_id', $companyId)
            ->where('created_at', '>=', now()->subDays($days))
            ->where('status', 'pending')
            ->count();

        $completedDeliveries = Delivery::where('company_id', $companyId)
            ->where('created_at', '>=', now()->subDays($days))
            ->where('status', 'completed')
            ->count();

        // Get delivery items list
        $deliveries = Delivery::where('company_id', $companyId)
            ->where('created_at', '>=', now()->subDays($days))
            ->orderBy('created_at', 'desc')
            ->get();

        // Chart data - daily deliveries
        $dailyStats = Delivery::where('company_id', $companyId)
            ->where('created_at', '>=', now()->subDays($days))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $labels = $dailyStats->pluck('date')->map(fn($d) => date('M d', strtotime($d)))->toArray();
        $data = $dailyStats->pluck('count')->toArray();

        $stats = [
            ['label' => 'Total Deliveries', 'value' => $totalDeliveries, 'color' => 'blue'],
            ['label' => 'Pending', 'value' => $pendingDeliveries, 'color' => 'yellow'],
            ['label' => 'Completed', 'value' => $completedDeliveries, 'color' => 'green'],
            ['label' => 'Avg per Day', 'value' => $days > 0 ? round($totalDeliveries / $days, 1) : 0, 'color' => 'purple'],
        ];

        return view('livewire.pages.superadmin.delivery-statistics', [
            'stats' => $stats,
            'labels' => $labels,
            'data' => $data,
            'deliveries' => $deliveries,
        ]);
    }
}
