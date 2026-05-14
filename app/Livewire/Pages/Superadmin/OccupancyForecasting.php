<?php

namespace App\Livewire\Pages\Superadmin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use App\Models\BookingRoom;
use App\Models\VehicleBooking;
use App\Models\Guestbook;
use App\Services\AI\LSTMClient;
use App\Services\WeatherService;
use Carbon\Carbon;

#[Layout('layouts.superadmin')]
#[Title('Occupancy Forecasting')]
class OccupancyForecasting extends Component
{
    public string $forecastType  = 'room';   // choice of room | vehicle | combined
    public int    $forecastDays  = 14;
    // public bool   $withWeather   = true;

    public function setForecastType(string $type): void
    {
        $this->forecastType = $type;
    }

    public function setForecastDays(int $days): void
    {
        $this->forecastDays = $days;
    }

    public function render()
    {
        $companyId = Auth::user()->company_id;

        // Historical occupancy (last 90 days)
        $roomHistory    = $this->getRoomHistory($companyId);
        $vehicleHistory = $this->getVehicleHistory($companyId);

        // LSTM forecast
        $lstm        = new LSTMClient();
        $isAvailable = $lstm->isAvailable();

        $roomForecast    = null;
        $vehicleForecast = null;

        if ($isAvailable) {
            if (in_array($this->forecastType, ['room', 'combined'])) {
                $result = $lstm->predict($roomHistory, $this->forecastDays, count($roomHistory) < 30);
                $roomForecast = $result['predictions'] ?? null;
            }
            if (in_array($this->forecastType, ['vehicle', 'combined'])) {
                $result = $lstm->predict($vehicleHistory, $this->forecastDays, count($vehicleHistory) < 30);
                $vehicleForecast = $result['predictions'] ?? null;
            }
        } else {
            // Fallback: simple moving-average projection
            if (in_array($this->forecastType, ['room', 'combined'])) {
                $roomForecast = $this->movingAverageForecast($roomHistory, $this->forecastDays);
            }
            if (in_array($this->forecastType, ['vehicle', 'combined'])) {
                $vehicleForecast = $this->movingAverageForecast($vehicleHistory, $this->forecastDays);
            }
        }

        // ── Weather (next 3 days from BMKG) 
        // $weather = null;
        // if ($this->withWeather) {
        //     $weatherService = new WeatherService();
        //     $weather = $weatherService->getForecast();
        // }

        // ── Chart data ────────────────────────────────────────────────────────
        $chartData = $this->buildChartData($roomForecast, $vehicleForecast);

        // ── Occupancy stats ───────────────────────────────────────────────────
        $stats = $this->buildStats($roomHistory, $vehicleHistory, $roomForecast, $vehicleForecast);

        return view('livewire.pages.superadmin.occupancy-forecasting', [
            'isLSTMAvailable' => $isAvailable,
            'roomForecast'    => $roomForecast,
            'vehicleForecast' => $vehicleForecast,
            'roomHistory'     => $roomHistory,
            'vehicleHistory'  => $vehicleHistory,
            'chartData'       => $chartData,
            'stats'           => $stats,
            'weather'         => null,
            'weatherInsight'  => null,
        ]);
    }

    // ── Private helpers ───────────────────────────────────────────────────────
    private function getRoomHistory(int $companyId): array
    {
        return BookingRoom::where('company_id', $companyId)
            ->where('created_at', '>=', now()->subDays(90))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupByRaw('DATE(created_at)')
            ->orderByRaw('DATE(created_at)')
            ->get()
            ->map(fn($r) => ['date' => $r->date, 'count' => (int) $r->count])
            ->toArray();
    }

    private function getVehicleHistory(int $companyId): array
    {
        return VehicleBooking::where('company_id', $companyId)
            ->where('created_at', '>=', now()->subDays(90))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupByRaw('DATE(created_at)')
            ->orderByRaw('DATE(created_at)')
            ->get()
            ->map(fn($r) => ['date' => $r->date, 'count' => (int) $r->count])
            ->toArray();
    }

    private function movingAverageForecast(array $history, int $days): array
    {
        if (empty($history)) {
            $avg = 3;
        } else {
            $window = array_slice($history, -14);
            $avg    = array_sum(array_column($window, 'count')) / count($window);
        }

        $lastDate = !empty($history) ? end($history)['date'] : date('Y-m-d');
        $forecast = [];

        for ($i = 1; $i <= $days; $i++) {
            $date  = date('Y-m-d', strtotime($lastDate . " +{$i} days"));
            $noise = $avg * 0.1 * (mt_rand(-10, 10) / 10);
            $forecast[] = [
                'date'        => $date,
                'predicted'   => round(max(0, $avg + $noise), 1),
                'lower_bound' => round(max(0, $avg * 0.8), 1),
                'upper_bound' => round($avg * 1.2, 1),
                'confidence'  => 0.65,
            ];
        }

        return $forecast;
    }

    private function buildChartData(?array $room, ?array $vehicle): array
    {
        $labels = [];
        $roomData    = [];
        $vehicleData = [];

        // Use whichever forecast is available for labels
        $base = $room ?? $vehicle ?? [];
        foreach ($base as $p) {
            $labels[]    = date('M d', strtotime($p['date']));
            $roomData[]  = $room    ? round($p['predicted'], 1) : null;
        }

        if ($vehicle) {
            foreach ($vehicle as $p) {
                $vehicleData[] = round($p['predicted'], 1);
            }
        }

        return compact('labels', 'roomData', 'vehicleData');
    }

    private function buildStats(array $roomHist, array $vehicleHist, ?array $roomFc, ?array $vehicleFc): array
    {
        $avgRoomHist    = $this->avg(array_column($roomHist, 'count'));
        $avgVehicleHist = $this->avg(array_column($vehicleHist, 'count'));
        $avgRoomFc      = $roomFc    ? $this->avg(array_column($roomFc, 'predicted'))    : null;
        $avgVehicleFc   = $vehicleFc ? $this->avg(array_column($vehicleFc, 'predicted')) : null;

        $roomTrend    = ($avgRoomFc && $avgRoomHist > 0)
            ? round(($avgRoomFc - $avgRoomHist) / $avgRoomHist * 100, 1) : 0;
        $vehicleTrend = ($avgVehicleFc && $avgVehicleHist > 0)
            ? round(($avgVehicleFc - $avgVehicleHist) / $avgVehicleHist * 100, 1) : 0;

        $peakDay = null;
        if ($roomFc) {
            $max = max(array_column($roomFc, 'predicted'));
            foreach ($roomFc as $p) {
                if (round($p['predicted'], 1) === round($max, 1)) {
                    $peakDay = date('D, d M', strtotime($p['date']));
                    break;
                }
            }
        }

        return [
            'avg_room_hist'    => round($avgRoomHist, 1),
            'avg_vehicle_hist' => round($avgVehicleHist, 1),
            'avg_room_fc'      => $avgRoomFc    ? round($avgRoomFc, 1)    : '—',
            'avg_vehicle_fc'   => $avgVehicleFc ? round($avgVehicleFc, 1) : '—',
            'room_trend'       => $roomTrend,
            'vehicle_trend'    => $vehicleTrend,
            'peak_day'         => $peakDay ?? '—',
            'total_room_fc'    => $roomFc    ? round(array_sum(array_column($roomFc, 'predicted')))    : '—',
            'total_vehicle_fc' => $vehicleFc ? round(array_sum(array_column($vehicleFc, 'predicted'))) : '—',
        ];
    }

    private function buildWeatherInsight(?array $weather, ?array $roomFc): ?array
    {
        if (!$weather || !$roomFc) return null;

        $insights = [];
        foreach ($weather['forecast'] as $day) {
            $date = $day['date'];
            // Find matching forecast day
            foreach ($roomFc as $fc) {
                if ($fc['date'] === $date) {
                    $rain    = $day['rain_chance'];
                    $weather_desc = $day['summary']['weather_desc'] ?? '';
                    $predicted = round($fc['predicted'], 1);

                    if ($rain >= 60) {
                        $insights[] = [
                            'date'    => $day['date_label'],
                            'icon'    => '🌧️',
                            'message' => "Rain likely ({$rain}% chance) on {$day['date_label']} — expect lower walk-in occupancy (~{$predicted} bookings).",
                            'type'    => 'warning',
                        ];
                    } elseif ($rain <= 20 && in_array($day['summary']['weather'] ?? 99, [0, 1, 2])) {
                        $insights[] = [
                            'date'    => $day['date_label'],
                            'icon'    => '☀️',
                            'message' => "Clear weather on {$day['date_label']} — good conditions for higher visitor turnout (~{$predicted} bookings).",
                            'type'    => 'positive',
                        ];
                    }
                    break;
                }
            }
        }

        return $insights ?: null;
    }

    private function avg(array $values): float
    {
        return count($values) > 0 ? array_sum($values) / count($values) : 0;
    }
}
