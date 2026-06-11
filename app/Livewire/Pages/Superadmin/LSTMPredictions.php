<?php

namespace App\Livewire\Pages\Superadmin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use App\Services\AI\LSTMClient;
use App\Services\AI\DataPreprocessor;

#[Layout('layouts.superadmin')]
#[Title('LSTM Predictions')]
class LSTMPredictions extends Component
{
    public int $forecastDays = 21;

    public function setForecastDays(int $days): void
    {
        $this->forecastDays = $days;
    }

    public function render()
    {
        try {
            $companyId       = Auth::user()->company_id;
            $lstmClient      = new LSTMClient();
            $isLSTMAvailable = $lstmClient->isAvailable();

            // Always predict visitor traffic (guestbook) on this page
            $preprocessor = new DataPreprocessor();
            $timeSeries   = $preprocessor->createTimeSeriesDataset('guestbook', $companyId);

            // ── Get predictions from LSTM service ─────────────────────────────
            $result = null;

            if ($isLSTMAvailable) {
                $result = $this->forecastDays === 21
                    ? $lstmClient->predict3Weeks($timeSeries, false)
                    : $lstmClient->predict($timeSeries, $this->forecastDays, false);
            }

            // ── Fallback to statistical model when LSTM is offline ────────────
            if (!$result || empty($result['predictions'])) {
                $fallback = $lstmClient->predictWithFallback($timeSeries, $this->forecastDays);
                $result   = array_merge($fallback, [
                    'data_source'    => 'statistical',
                    'title'          => 'Booking Predictions',
                    'description'    => null,
                    'weekly_summary' => $this->buildWeeklySummary($fallback['predictions'] ?? []),
                ]);
            }

            // ── Build chart arrays ────────────────────────────────────────────
            $predictions     = $result['predictions'];
            $dailyLabels     = array_map(fn($p) => date('d/m', strtotime($p['date'])), $predictions);
            $dailyPredicted  = array_map(fn($p) => round($p['predicted'], 1), $predictions);
            $dailyLowerBound = array_map(fn($p) => round($p['lower_bound'], 1), $predictions);
            $dailyUpperBound = array_map(fn($p) => round($p['upper_bound'], 1), $predictions);

            // ── Weekly summary ────────────────────────────────────────────────
            $weeklyData = null;
            if (!empty($result['weekly_summary'])) {
                $weeklyData = [
                    'labels'   => array_map(fn($w) => __('app.week_label') . ' ' . $w['week'], $result['weekly_summary']),
                    'totals'   => array_map(fn($w) => round($w['total_predicted'], 0), $result['weekly_summary']),
                    'averages' => array_map(fn($w) => round($w['avg_predicted'], 1), $result['weekly_summary']),
                ];
            }

            // ── Stats cards ───────────────────────────────────────────────────
            $totalPredicted = array_sum($dailyPredicted);
            $avgDaily       = $totalPredicted / max(1, count($dailyPredicted));
            $avgConfidence  = array_sum(array_column($predictions, 'confidence')) / max(1, count($predictions));
            $maxDay         = !empty($dailyPredicted) ? max($dailyPredicted) : 0;

            $stats = [
                ['label' => __('app.total_predicted'), 'value' => number_format($totalPredicted, 0), 'color' => 'blue',   'icon' => 'chart-bar'],
                ['label' => __('app.avg_per_day'),      'value' => number_format($avgDaily, 1),        'color' => 'green',  'icon' => 'calculator'],
                ['label' => __('app.peak_day'),         'value' => number_format($maxDay, 0),           'color' => 'yellow', 'icon' => 'arrow-trending-up'],
                ['label' => __('app.confidence'),       'value' => number_format($avgConfidence * 100, 1) . '%', 'color' => 'purple', 'icon' => 'check-badge'],
            ];

            return view('livewire.pages.superadmin.lstm-predictions', [
                'isLSTMAvailable' => $isLSTMAvailable,
                'predictions'     => $predictions,
                'weeklyData'      => $weeklyData,
                'dailyLabels'     => $dailyLabels,
                'dailyPredicted'  => $dailyPredicted,
                'dailyLowerBound' => $dailyLowerBound,
                'dailyUpperBound' => $dailyUpperBound,
                'stats'           => $stats,
                'rmse'            => $result['rmse'] ?? ($result['metrics']['rmse'] ?? 0),
                'dataSource'      => $result['data_source'] ?? 'statistical',
                'title'           => $result['title'] ?? 'Booking Predictions',
                'description'     => $result['description'] ?? null,
            ]);

        } catch (\Exception $e) {
            \Log::error('LSTMPredictions render failed', ['error' => $e->getMessage()]);

            return view('livewire.pages.superadmin.lstm-predictions', [
                'isLSTMAvailable' => false,
                'predictions'     => [],
                'weeklyData'      => null,
                'dailyLabels'     => [],
                'dailyPredicted'  => [],
                'dailyLowerBound' => [],
                'dailyUpperBound' => [],
                'stats'           => $this->getEmptyStats(),
                'rmse'            => 0,
                'dataSource'      => 'error',
                'title'           => 'Booking Predictions',
                'description'     => null,
            ]);
        }
    }

    private function buildWeeklySummary(array $predictions): array
    {
        if (count($predictions) < 7) return [];

        $summary = [];
        foreach (array_chunk($predictions, 7) as $i => $week) {
            $total = array_sum(array_column($week, 'predicted'));
            $summary[] = [
                'week'            => $i + 1,
                'start_date'      => $week[0]['date'],
                'end_date'        => end($week)['date'],
                'total_predicted' => round($total, 2),
                'avg_predicted'   => round($total / count($week), 2),
            ];
        }
        return $summary;
    }

    private function getEmptyStats(): array
    {
        return [
            ['label' => __('app.total_predicted'), 'value' => '0',  'color' => 'blue',   'icon' => 'chart-bar'],
            ['label' => __('app.avg_per_day'),      'value' => '0',  'color' => 'green',  'icon' => 'calculator'],
            ['label' => __('app.peak_day'),         'value' => '0',  'color' => 'yellow', 'icon' => 'arrow-trending-up'],
            ['label' => __('app.confidence'),       'value' => '0%', 'color' => 'purple', 'icon' => 'check-badge'],
        ];
    }
}
