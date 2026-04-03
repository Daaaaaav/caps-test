<?php

namespace App\Livewire\Pages\Superadmin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use App\Services\AI\PredictionService;

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
        try {
            $companyId = Auth::user()->company_id;
            $predictionService = new PredictionService();

            // Get real AI-detected anomalies
            $roomAnomalies = $predictionService->detectAnomalies('room_booking', $companyId);
            $vehicleAnomalies = $predictionService->detectAnomalies('vehicle_booking', $companyId);
            $guestbookAnomalies = $predictionService->detectAnomalies('guestbook', $companyId);
            $deliveryAnomalies = $predictionService->detectAnomalies('delivery', $companyId);

            // Combine and format alerts
            $allAlerts = [];
            
            foreach ($roomAnomalies as $anomaly) {
                $allAlerts[] = [
                    'severity' => $anomaly['severity'],
                    'message' => '[Room Bookings] ' . $anomaly['message'],
                    'time' => 'Real-time',
                    'icon' => $this->getIconForSeverity($anomaly['severity']),
                    'recommendation' => $anomaly['recommendation'] ?? null,
                ];
            }

            foreach ($vehicleAnomalies as $anomaly) {
                $allAlerts[] = [
                    'severity' => $anomaly['severity'],
                    'message' => '[Vehicle Bookings] ' . $anomaly['message'],
                    'time' => 'Real-time',
                    'icon' => $this->getIconForSeverity($anomaly['severity']),
                    'recommendation' => $anomaly['recommendation'] ?? null,
                ];
            }

            foreach ($guestbookAnomalies as $anomaly) {
                $allAlerts[] = [
                    'severity' => $anomaly['severity'],
                    'message' => '[Visitors] ' . $anomaly['message'],
                    'time' => 'Real-time',
                    'icon' => $this->getIconForSeverity($anomaly['severity']),
                    'recommendation' => $anomaly['recommendation'] ?? null,
                ];
            }

            foreach ($deliveryAnomalies as $anomaly) {
                $allAlerts[] = [
                    'severity' => $anomaly['severity'],
                    'message' => '[Deliveries] ' . $anomaly['message'],
                    'time' => 'Real-time',
                    'icon' => $this->getIconForSeverity($anomaly['severity']),
                    'recommendation' => $anomaly['recommendation'] ?? null,
                ];
            }

            // Add system health check if no anomalies
            if (empty($allAlerts)) {
                $allAlerts[] = [
                    'severity' => 'low',
                    'message' => 'All systems operating normally - no anomalies detected',
                    'time' => now()->format('H:i'),
                    'icon' => 'check-circle',
                    'recommendation' => null,
                ];
            }

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
        } catch (\Exception $e) {
            $this->dispatch('toast', 
                type: 'error',
                title: 'Error',
                message: 'Failed to retrieve security reports: ' . $e->getMessage(),
                duration: 4000
            );

            return view('livewire.pages.superadmin.a-i-security-reports', [
                'alerts' => [],
                'stats' => [
                    ['label' => 'Total Alerts', 'value' => 0, 'color' => 'blue'],
                    ['label' => 'High Priority', 'value' => 0, 'color' => 'red'],
                    ['label' => 'Medium Priority', 'value' => 0, 'color' => 'yellow'],
                    ['label' => 'Low Priority', 'value' => 0, 'color' => 'green'],
                ],
            ]);
        }
    }

    private function getIconForSeverity(string $severity): string
    {
        return match($severity) {
            'high' => 'exclamation-triangle',
            'medium' => 'shield-exclamation',
            default => 'check-circle',
        };
    }
}
