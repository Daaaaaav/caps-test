<?php

namespace App\Livewire\Pages\Superadmin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;

#[Layout('layouts.superadmin')]
#[Title('AI Security Reports')]
class AISecurityReports extends Component
{
    public $selectedSeverity = 'all';
    public $autoRefresh = true;

    public function setSeverity($level)
    {
        $this->selectedSeverity = $level;
    }

    public function toggleAutoRefresh()
    {
        $this->autoRefresh = !$this->autoRefresh;
    }

    public function render()
    {
        $companyId = Auth::user()->company_id;

        // Simulated AI alerts with severity levels
        $allAlerts = [
            ['severity' => 'high', 'message' => 'Unusual booking pattern detected', 'time' => '2 min ago', 'icon' => 'exclamation-triangle'],
            ['severity' => 'medium', 'message' => 'Multiple failed login attempts', 'time' => '15 min ago', 'icon' => 'shield-exclamation'],
            ['severity' => 'low', 'message' => 'System performance normal', 'time' => '1 hour ago', 'icon' => 'check-circle'],
            ['severity' => 'high', 'message' => 'Suspicious vehicle booking request', 'time' => '3 hours ago', 'icon' => 'exclamation-triangle'],
            ['severity' => 'medium', 'message' => 'Visitor traffic spike detected', 'time' => '5 hours ago', 'icon' => 'chart-bar'],
            ['severity' => 'low', 'message' => 'No anomalies in room bookings', 'time' => '1 day ago', 'icon' => 'check-circle'],
        ];

        // Filter by severity
        if ($this->selectedSeverity !== 'all') {
            $alerts = array_filter($allAlerts, fn($a) => $a['severity'] === $this->selectedSeverity);
        } else {
            $alerts = $allAlerts;
        }

        // Stats
        $stats = [
            ['label' => 'Total Alerts', 'value' => count($allAlerts), 'color' => 'blue'],
            ['label' => 'High Priority', 'value' => count(array_filter($allAlerts, fn($a) => $a['severity'] === 'high')), 'color' => 'red'],
            ['label' => 'Medium Priority', 'value' => count(array_filter($allAlerts, fn($a) => $a['severity'] === 'medium')), 'color' => 'yellow'],
            ['label' => 'Low Priority', 'value' => count(array_filter($allAlerts, fn($a) => $a['severity'] === 'low')), 'color' => 'green'],
        ];

        return view('livewire.pages.superadmin.a-i-security-reports', [
            'alerts' => $alerts,
            'stats' => $stats,
        ]);
    }
}
