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

        // Get guestbook stats
        $totalVisitors = Guestbook::where('company_id', $companyId)
            ->where('created_at', '>=', now()->subDays($days))
            ->count();

        $checkedIn = Guestbook::where('company_id', $companyId)
            ->where('created_at', '>=', now()->subDays($days))
            ->whereNotNull('jam_in')
            ->whereNull('jam_out')
            ->count();

        $checkedOut = Guestbook::where('company_id', $companyId)
            ->where('created_at', '>=', now()->subDays($days))
            ->whereNotNull('jam_out')
            ->count();

        // TO DO: Change dummy retrieval data after done with AI
        if ($totalVisitors == 0) {
            $totalVisitors = match($this->timeRange) {
                '7days' => 42,
                '30days' => 180,
                '90days' => 540,
                default => 42,
            };
            $checkedIn = round($totalVisitors * 0.15);
            $checkedOut = $totalVisitors - $checkedIn;
        }

        // Get guestbook items list
        $guestbooks = Guestbook::where('company_id', $companyId)
            ->where('created_at', '>=', now()->subDays($days))
            ->orderBy('created_at', 'desc')
            ->get();

        // Chart data - daily visitors
        $dailyStats = Guestbook::where('company_id', $companyId)
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
                $data = [5, 7, 6, 8, 5, 4, 7];
            } elseif ($this->timeRange === '30days') {
                for ($i = 29; $i >= 0; $i--) {
                    $labels[] = now()->subDays($i)->format('M d');
                    $data[] = rand(3, 9);
                }
            } else {
                for ($i = 89; $i >= 0; $i -= 3) {
                    $labels[] = now()->subDays($i)->format('M d');
                    $data[] = rand(15, 25);
                }
            }
        } else {
            $labels = $dailyStats->pluck('date')->map(fn($d) => date('M d', strtotime($d)))->toArray();
            $data = $dailyStats->pluck('count')->toArray();
        }

        $stats = [
            ['label' => 'Total Visitors', 'value' => $totalVisitors, 'color' => 'blue'],
            ['label' => 'Currently In', 'value' => $checkedIn, 'color' => 'yellow'],
            ['label' => 'Checked Out', 'value' => $checkedOut, 'color' => 'green'],
            ['label' => 'Avg per Day', 'value' => $days > 0 ? round($totalVisitors / $days, 1) : 0, 'color' => 'purple'],
        ];

        return view('livewire.pages.superadmin.guestbook-statistics', [
            'stats' => $stats,
            'labels' => $labels,
            'data' => $data,
            'guestbooks' => $guestbooks,
        ]);
    }
}
