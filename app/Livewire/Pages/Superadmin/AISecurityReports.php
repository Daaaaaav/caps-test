<?php

namespace App\Livewire\Pages\Superadmin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Services\WazuhAlertService;

#[Layout('layouts.superadmin')]
#[Title('Wazuh Security Reports')]
class AISecurityReports extends Component
{
    public string $selectedSeverity = 'all';

    public bool $autoRefresh = true;

    public function setSeverity(string $level): void
    {
        $this->selectedSeverity = $level;
    }

    public function toggleAutoRefresh(): void
    {
        $this->autoRefresh = !$this->autoRefresh;
    }

    public function render()
    {
        try {

            $report = app(WazuhAlertService::class)
                ->getRecentAlerts(25);

            $alerts = collect($report['alerts']);

            if ($this->selectedSeverity !== 'all') {
                $alerts = $alerts->where(
                    'severity',
                    $this->selectedSeverity
                );
            }

            return view(
                'livewire.pages.superadmin.a-i-security-reports',
                [
                    'alerts' => $alerts->values()->toArray(),
                    ...$report,
                ]
            );

        } catch (\Throwable $e) {

            report($e);

            return view(
                'livewire.pages.superadmin.a-i-security-reports',
                [
                    'alerts' => [],
                    'stats' => [],
                    'source_label' => 'Unavailable',
                    'source_host' => null,
                    'api_endpoints' => [],
                    'last_updated' => null,
                    'available' => false,
                ]
            );
        }
    }
}