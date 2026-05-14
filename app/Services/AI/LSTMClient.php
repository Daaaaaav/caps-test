<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LSTMClient
{
    private string $baseUrl;
    private int $timeout;
    private int $minimumDataPoints = 45;

    public function __construct()
    {
        $this->baseUrl  = env('LSTM_SERVICE_URL', 'http://127.0.0.1:8001');
        $this->timeout  = env('LSTM_SERVICE_TIMEOUT', 30);
    }

    /**
     * Check if the LSTM service is reachable.
     */
    public function isAvailable(): bool
    {
        try {
            $response = Http::timeout(2)->get($this->baseUrl . '/');
            return $response->successful();
        } catch (\Exception $e) {
            Log::warning('LSTM service unavailable', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Generate AI forecast predictions.
     *
     * @param array $timeSeries  Array of ['date' => 'Y-m-d', 'count' => int]
     * @param int   $forecastDays
     * @param bool  $useDummyData
     * @return array|null
     */
    public function predict(array $timeSeries, int $forecastDays = 7, bool $useDummyData = false): ?array
    {
        try {
            if (!$useDummyData && count($timeSeries) < $this->minimumDataPoints) {
                Log::warning('Insufficient historical data for LSTM forecast', [
                    'required' => $this->minimumDataPoints,
                    'received' => count($timeSeries),
                ]);
                return null;
            }

            $data = array_map(fn($p) => [
                'date'  => $p['date'],
                'count' => (float) $p['count'],
            ], $timeSeries);

            $payload = [
                'data'           => $data,
                'forecast_days'  => $forecastDays,
                'use_dummy_data' => $useDummyData,
            ];

            $response = Http::timeout($this->timeout)
                ->post($this->baseUrl . '/predict', $payload);

            if (!$response->successful()) {
                Log::warning('LSTM service returned unsuccessful response', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return null;
            }

            $result = $response->json();

            Log::info('LSTM prediction generated', [
                'model'            => $result['model'] ?? 'unknown',
                'rmse'             => $result['metrics']['rmse'] ?? null,
                'training_samples' => $result['training_samples'] ?? null,
            ]);

            return [
                'method'           => 'lstm',
                'model'            => $result['model'] ?? 'Improved LSTM Forecast Model',
                'rmse'             => $result['metrics']['rmse'] ?? $result['rmse'] ?? 0,
                'metrics'          => $result['metrics'] ?? ['rmse' => 0, 'mae' => 0, 'mape' => 0],
                'features_used'    => $result['features_used'] ?? [],
                'predictions'      => $result['predictions'] ?? [],
                'data_source'      => $result['data_source'] ?? 'unknown',
                'weekly_summary'   => $result['weekly_summary'] ?? null,
                'training_samples' => $result['training_samples'] ?? 0,
                'test_samples'     => $result['test_samples'] ?? 0,
            ];

        } catch (\Exception $e) {
            Log::error('LSTM prediction failed', [
                'error'        => $e->getMessage(),
                'url'          => $this->baseUrl,
                'forecast_days'=> $forecastDays,
            ]);
            return null;
        }
    }

    /**
     * Generate a 21-day (3-week) forecast.
     *
     * @param array $timeSeries
     * @param bool  $useDummyData
     * @return array|null
     */
    public function predict3Weeks(array $timeSeries = [], bool $useDummyData = false): ?array
    {
        try {
            if (empty($timeSeries) || count($timeSeries) < $this->minimumDataPoints) {
                $useDummyData = true;
                $timeSeries   = [['date' => date('Y-m-d'), 'count' => 0]];
            }

            $data = array_map(fn($p) => [
                'date'  => $p['date'],
                'count' => (float) $p['count'],
            ], $timeSeries);

            $payload = [
                'data'           => $data,
                'forecast_days'  => 21,
                'use_dummy_data' => $useDummyData,
            ];

            $response = Http::timeout($this->timeout)
                ->post($this->baseUrl . '/predict-3weeks', $payload);

            if (!$response->successful()) {
                Log::warning('3-week forecast request failed', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return null;
            }

            return $response->json();

        } catch (\Exception $e) {
            Log::error('LSTM 3-week prediction failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Call the demo endpoint (always uses dummy data).
     */
    public function getDemo(): ?array
    {
        try {
            $response = Http::timeout($this->timeout)->get($this->baseUrl . '/demo');

            if (!$response->successful()) {
                Log::warning('LSTM demo endpoint failed', ['status' => $response->status()]);
                return null;
            }

            return $response->json();

        } catch (\Exception $e) {
            Log::error('LSTM demo failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Try LSTM first; fall back to simple moving average if unavailable.
     */
    public function predictWithFallback(array $timeSeries, int $forecastDays = 7): array
    {
        $lstmResult = $this->predict($timeSeries, $forecastDays);

        if ($lstmResult !== null) {
            return $lstmResult;
        }

        Log::info('Using fallback moving average forecast');
        return $this->simpleMovingAverage($timeSeries, $forecastDays);
    }

    /**
     * Simple moving-average fallback when LSTM is unavailable.
     */
    private function simpleMovingAverage(array $timeSeries, int $forecastDays): array
    {
        if (empty($timeSeries)) {
            return [
                'method'      => 'fallback',
                'model'       => 'Simple Moving Average',
                'metrics'     => ['rmse' => 0, 'mae' => 0, 'mape' => 0],
                'predictions' => [],
            ];
        }

        $windowSize = min(7, count($timeSeries));
        $recentData = array_slice($timeSeries, -$windowSize);
        $avgCount   = array_sum(array_column($recentData, 'count')) / $windowSize;

        // Simple trend: slope over the window
        $trend = 0;
        if (count($recentData) >= 2) {
            $first = $recentData[0]['count'];
            $last  = end($recentData)['count'];
            $trend = ($last - $first) / count($recentData);
        }

        $predictions = [];
        $lastDate    = end($timeSeries)['date'];

        for ($i = 1; $i <= $forecastDays; $i++) {
            $nextDate   = date('Y-m-d', strtotime($lastDate . " +{$i} days"));
            $prediction = $avgCount + ($trend * $i);

            // Weekend adjustment
            if (date('N', strtotime($nextDate)) >= 6) {
                $prediction *= 0.9;
            }

            $prediction = max(0, round($prediction, 1));

            $predictions[] = [
                'date'        => $nextDate,
                'predicted'   => $prediction,
                'lower_bound' => max(0, round($prediction * 0.8, 1)),
                'upper_bound' => round($prediction * 1.2, 1),
                'confidence'  => 0.60,
            ];
        }

        return [
            'method'       => 'fallback',
            'model'        => 'Simple Moving Average',
            'metrics'      => ['rmse' => 0, 'mae' => 0, 'mape' => 0],
            'predictions'  => $predictions,
            'features_used'=> ['moving_average', 'trend_estimation', 'weekend_adjustment'],
        ];
    }
}
