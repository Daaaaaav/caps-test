<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * LSTM Client Service
 * 
 * Communicates with the Python LSTM microservice for advanced predictions
 */
class LSTMClient
{
    private string $baseUrl;
    private int $timeout;

    public function __construct()
    {
        $this->baseUrl = env('LSTM_SERVICE_URL', 'http://127.0.0.1:8001');
        $this->timeout = env('LSTM_SERVICE_TIMEOUT', 30);
    }

    /**
     * Check if LSTM service is available
     */
    public function isAvailable(): bool
    {
        try {
            $response = Http::timeout(2)->get($this->baseUrl . '/');
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get LSTM predictions from Python service
     * 
     * @param array $timeSeries Array of ['date' => 'Y-m-d', 'count' => int]
     * @param int $forecastDays Number of days to forecast
     * @param bool $useDummyData Use dummy data for demonstration
     * @return array|null Predictions or null if service unavailable
     */
    public function predict(array $timeSeries, int $forecastDays = 7, bool $useDummyData = false): ?array
    {
        try {
            // Format data for LSTM service
            $data = array_map(function($point) {
                return [
                    'date' => $point['date'],
                    'count' => (float) $point['count'],
                ];
            }, $timeSeries);

            $payload = [
                'data' => $data,
                'forecast_days' => $forecastDays,
                'use_dummy_data' => $useDummyData,
            ];

            // Call Python LSTM service
            $response = Http::timeout($this->timeout)
                ->post($this->baseUrl . '/predict', $payload);

            if (!$response->successful()) {
                Log::warning('LSTM service returned error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            }

            $result = $response->json();

            return [
                'rmse' => $result['rmse'] ?? 0,
                'predictions' => $result['predictions'] ?? [],
                'data_source' => $result['data_source'] ?? 'unknown',
                'weekly_summary' => $result['weekly_summary'] ?? null,
            ];

        } catch (\Exception $e) {
            Log::error('LSTM service communication failed', [
                'error' => $e->getMessage(),
                'url' => $this->baseUrl,
            ]);
            return null;
        }
    }

    /**
     * Get 3-week predictions with dummy data demonstration
     * 
     * @param array $timeSeries
     * @param bool $useDummyData Force use of dummy data
     * @return array|null
     */
    public function predict3Weeks(array $timeSeries = [], bool $useDummyData = false): ?array
    {
        try {
            // If no data provided or insufficient, use dummy data
            if (empty($timeSeries) || count($timeSeries) < 30) {
                $useDummyData = true;
                $timeSeries = [['date' => date('Y-m-d'), 'count' => 0]]; // Placeholder
            }

            $data = array_map(function($point) {
                return [
                    'date' => $point['date'],
                    'count' => (float) $point['count'],
                ];
            }, $timeSeries);

            $payload = [
                'data' => $data,
                'forecast_days' => 21,
                'use_dummy_data' => $useDummyData,
            ];

            $response = Http::timeout($this->timeout)
                ->post($this->baseUrl . '/predict-3weeks', $payload);

            if (!$response->successful()) {
                return null;
            }

            return $response->json();

        } catch (\Exception $e) {
            Log::error('LSTM 3-week prediction failed', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Get demo prediction (always uses dummy data)
     * 
     * @return array|null
     */
    public function getDemo(): ?array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->get($this->baseUrl . '/demo');

            if (!$response->successful()) {
                return null;
            }

            return $response->json();

        } catch (\Exception $e) {
            Log::error('LSTM demo failed', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Get predictions with fallback to simple moving average
     * 
     * @param array $timeSeries
     * @param int $forecastDays
     * @return array Always returns predictions (uses fallback if LSTM unavailable)
     */
    public function predictWithFallback(array $timeSeries, int $forecastDays = 7): array
    {
        // Try LSTM first
        $lstmResult = $this->predict($timeSeries, $forecastDays);

        if ($lstmResult !== null) {
            return [
                'method' => 'lstm',
                'rmse' => $lstmResult['rmse'],
                'predictions' => $lstmResult['predictions'],
            ];
        }

        // Fallback to simple moving average
        return $this->simpleMovingAverage($timeSeries, $forecastDays);
    }

    /**
     * Simple moving average fallback
     */
    private function simpleMovingAverage(array $timeSeries, int $forecastDays): array
    {
        if (empty($timeSeries)) {
            return [
                'method' => 'fallback',
                'rmse' => 0,
                'predictions' => [],
            ];
        }

        $windowSize = min(7, count($timeSeries));
        $recentData = array_slice($timeSeries, -$windowSize);
        $avgCount = array_sum(array_column($recentData, 'count')) / $windowSize;

        $predictions = [];
        $lastDate = end($timeSeries)['date'];

        for ($i = 1; $i <= $forecastDays; $i++) {
            $nextDate = date('Y-m-d', strtotime($lastDate . " +{$i} days"));
            
            $predictions[] = [
                'date' => $nextDate,
                'predicted' => max(0, round($avgCount)),
                'lower_bound' => max(0, round($avgCount * 0.8)),
                'upper_bound' => round($avgCount * 1.2),
                'confidence' => 0.6,
            ];
        }

        return [
            'method' => 'fallback',
            'rmse' => 0,
            'predictions' => $predictions,
        ];
    }
}
