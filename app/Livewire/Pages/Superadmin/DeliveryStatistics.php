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
        try {
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

            // TO DO: Change dummy retrieval data after done with AI
            if ($totalDeliveries == 0) {
                $totalDeliveries = match($this->timeRange) {
                    '7days' => 28,
                    '30days' => 120,
                    '90days' => 360,
                    default => 28,
                };
                $pendingDeliveries = round($totalDeliveries * 0.2);
                $completedDeliveries = $totalDeliveries - $pendingDeliveries;
            }

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

            if ($dailyStats->isEmpty()) {
                // Generate dummy data based on time range
                $labels = [];
                $data = [];
                
                if ($this->timeRange === '7days') {
                    $labels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
                    $data = [3, 5, 4, 6, 3, 4, 3];
                } elseif ($this->timeRange === '30days') {
                    for ($i = 29; $i >= 0; $i--) {
                        $labels[] = now()->subDays($i)->format('M d');
                        $data[] = rand(2, 6);
                    }
                } else {
                    for ($i = 89; $i >= 0; $i -= 3) {
                        $labels[] = now()->subDays($i)->format('M d');
                        $data[] = rand(8, 15);
                    }
                }
            } else {
                $labels = $dailyStats->pluck('date')->map(fn($d) => date('M d', strtotime($d)))->toArray();
                $data = $dailyStats->pluck('count')->toArray();
            }

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
        } catch (\Exception $e) {
            $this->dispatch('toast', 
                type: 'error',
                title: 'Error',
                message: 'Failed to retrieve delivery data: ' . $e->getMessage(),
                duration: 4000
            );

            return view('livewire.pages.superadmin.delivery-statistics', [
                'stats' => [
                    ['label' => 'Total Deliveries', 'value' => 0, 'color' => 'blue'],
                    ['label' => 'Pending', 'value' => 0, 'color' => 'yellow'],
                    ['label' => 'Completed', 'value' => 0, 'color' => 'green'],
                    ['label' => 'Avg per Day', 'value' => 0, 'color' => 'purple'],
                ],
                'labels' => [],
                'data' => [],
                'deliveries' => collect([]),
            ]);
        }
    }
}
