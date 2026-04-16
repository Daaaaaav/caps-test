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
    public $predictionType = 'room_booking';
    public $useDummyData = false;
    public $forecastDays = 21;
    public $isLoading = false;

    public function setPredictionType($type)
    {
        $this->predictionType = $type;
    }

    public function toggleDummyData()
    {
        $this->useDummyData = !$this->useDummyData;
    }

    public function setForecastDays($days)
    {
        $this->forecastDays = $days;
    }

    public function render()
    {
        try {
            $companyId = Auth::user()->company_id;
            $lstmClient = new LSTMClient();

            // Check if LSTM service is available
            $isLSTMAvailable = $lstmClient->isAvailable();

            if (!$isLSTMAvailable) {
                return view('livewire.pages.superadmin.lstm-predictions', [
                    'isLSTMAvailable' => false,
                    'predictions' => null,
                    'weeklyData' => null,
                    'dailyLabels' => [],
                    'dailyPredicted' => [],
                    'dailyLowerBound' => [],
                    'dailyUpperBound' => [],
                    'stats' => $this->getEmptyStats(),
                    'rmse' => 0,
                    'dataSource' => 'unknown',
                    'title' => 'LSTM Model Predictions',
                    'description' => null,
                ]);
            }

            // Get predictions
            if ($this->forecastDays == 21) {
                // Use 3-week specialized endpoint
                if ($this->useDummyData) {
                    $result = $lstmClient->getDemo();
                } else {
                    $preprocessor = new DataPreprocessor();
                    $timeSeries = $preprocessor->createTimeSeriesDataset($this->predictionType, $companyId, 90);
                    $result = $lstmClient->predict3Weeks($timeSeries, false);
                }
            } else {
                // Use standard prediction
                $preprocessor = new DataPreprocessor();
                $timeSeries = $preprocessor->createTimeSeriesDataset($this->predictionType, $companyId, 90);
                $result = $lstmClient->predict($timeSeries, $this->forecastDays, $this->useDummyData);
            }

            if (!$result || empty($result['predictions'])) {
                return view('livewire.pages.superadmin.lstm-predictions', [
                    'isLSTMAvailable' => true,
                    'predictions' => null,
                    'weeklyData' => null,
                    'dailyLabels' => [],
                    'dailyPredicted' => [],
                    'dailyLowerBound' => [],
                    'dailyUpperBound' => [],
                    'stats' => $this->getEmptyStats(),
                    'rmse' => 0,
                    'dataSource' => 'unknown',
                    'title' => 'LSTM Model Predictions',
                    'description' => null,
                ]);
            }

            // Prepare chart data
            $predictions = $result['predictions'];
            $dailyLabels = array_map(fn($p) => date('M d', strtotime($p['date'])), $predictions);
            $dailyPredicted = array_map(fn($p) => round($p['predicted'], 1), $predictions);
            $dailyLowerBound = array_map(fn($p) => round($p['lower_bound'], 1), $predictions);
            $dailyUpperBound = array_map(fn($p) => round($p['upper_bound'], 1), $predictions);

            // Prepare weekly data if available
            $weeklyData = null;
            if (isset($result['weekly_summary'])) {
                $weeklyData = [
                    'labels' => array_map(fn($w) => 'Week ' . $w['week'], $result['weekly_summary']),
                    'totals' => array_map(fn($w) => round($w['total_predicted'], 0), $result['weekly_summary']),
                    'averages' => array_map(fn($w) => round($w['avg_predicted'], 1), $result['weekly_summary']),
                ];
            }

            // Calculate statistics
            $totalPredicted = array_sum($dailyPredicted);
            $avgDaily = $totalPredicted / count($dailyPredicted);
            $avgConfidence = array_sum(array_column($predictions, 'confidence')) / count($predictions);
            $maxDay = max($dailyPredicted);
            $minDay = min($dailyPredicted);

            $stats = [
                [
                    'label' => 'Total Predicted',
                    'value' => number_format($totalPredicted, 0),
                    'color' => 'blue',
                    'icon' => 'chart-bar'
                ],
                [
                    'label' => 'Avg per Day',
                    'value' => number_format($avgDaily, 1),
                    'color' => 'green',
                    'icon' => 'calculator'
                ],
                [
                    'label' => 'Peak Day',
                    'value' => number_format($maxDay, 0),
                    'color' => 'yellow',
                    'icon' => 'arrow-trending-up'
                ],
                [
                    'label' => 'Confidence',
                    'value' => number_format($avgConfidence * 100, 1) . '%',
                    'color' => 'purple',
                    'icon' => 'check-badge'
                ],
            ];

            return view('livewire.pages.superadmin.lstm-predictions', [
                'isLSTMAvailable' => true,
                'predictions' => $predictions,
                'weeklyData' => $weeklyData,
                'dailyLabels' => $dailyLabels,
                'dailyPredicted' => $dailyPredicted,
                'dailyLowerBound' => $dailyLowerBound,
                'dailyUpperBound' => $dailyUpperBound,
                'stats' => $stats,
                'rmse' => $result['rmse'] ?? 0,
                'dataSource' => $result['data_source'] ?? 'unknown',
                'title' => $result['title'] ?? 'LSTM Model Predictions',
                'description' => $result['description'] ?? null,
            ]);

        } catch (\Exception $e) {
            $this->dispatch('toast', 
                type: 'error',
                title: 'Error',
                message: 'Failed to generate predictions: ' . $e->getMessage(),
                duration: 4000
            );

            return view('livewire.pages.superadmin.lstm-predictions', [
                'isLSTMAvailable' => false,
                'predictions' => null,
                'weeklyData' => null,
                'dailyLabels' => [],
                'dailyPredicted' => [],
                'dailyLowerBound' => [],
                'dailyUpperBound' => [],
                'stats' => $this->getEmptyStats(),
                'rmse' => 0,
                'dataSource' => 'unknown',
                'title' => 'LSTM Model Predictions',
                'description' => null,
            ]);
        }
    }

    private function getEmptyStats(): array
    {
        return [
            ['label' => 'Total Predicted', 'value' => '0', 'color' => 'blue', 'icon' => 'chart-bar'],
            ['label' => 'Avg per Day', 'value' => '0', 'color' => 'green', 'icon' => 'calculator'],
            ['label' => 'Peak Day', 'value' => '0', 'color' => 'yellow', 'icon' => 'arrow-trending-up'],
            ['label' => 'Confidence', 'value' => '0%', 'color' => 'purple', 'icon' => 'check-badge'],
        ];
    }
}
