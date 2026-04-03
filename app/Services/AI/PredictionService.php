<?php

namespace App\Services\AI;

use Carbon\Carbon;

/**
 * AI Prediction Service
 * 
 * Uses preprocessed data to make predictions and generate insights
 */
class PredictionService
{
    private DataPreprocessor $preprocessor;

    public function __construct()
    {
        $this->preprocessor = new DataPreprocessor();
    }

    /**
     * Predict future booking demand using LSTM or fallback methods
     */
    public function predictBookingDemand(string $type, int $companyId, int $forecastDays = 7): array
    {
        // Get historical data
        $timeSeries = $this->preprocessor->createTimeSeriesDataset($type, $companyId, 90);
        
        if (empty($timeSeries)) {
            return $this->getEmptyForecast($forecastDays);
        }

        // Try LSTM prediction first
        $lstmClient = new LSTMClient();
        $result = $lstmClient->predictWithFallback($timeSeries, $forecastDays);

        // Format predictions
        $forecast = [];
        foreach ($result['predictions'] as $prediction) {
            $forecast[] = [
                'date' => $prediction['date'],
                'predicted_count' => (int) $prediction['predicted'],
                'lower_bound' => (int) ($prediction['lower_bound'] ?? $prediction['predicted'] * 0.8),
                'upper_bound' => (int) ($prediction['upper_bound'] ?? $prediction['predicted'] * 1.2),
                'confidence' => $prediction['confidence'] ?? 0.7,
                'day_of_week' => date('l', strtotime($prediction['date'])),
                'method' => $result['method'], // 'lstm' or 'fallback'
            ];
        }
        
        return $forecast;
    }

    /**
     * Detect anomalies in booking patterns
     */
    public function detectAnomalies(string $type, int $companyId): array
    {
        $features = match($type) {
            'room_booking' => $this->preprocessor->preprocessRoomBookings($companyId),
            'vehicle_booking' => $this->preprocessor->preprocessVehicleBookings($companyId),
            'guestbook' => $this->preprocessor->preprocessGuestbookData($companyId),
            'delivery' => $this->preprocessor->preprocessDeliveryData($companyId),
            default => [],
        };

        $anomalies = [];

        // Check for unusual time patterns
        if (isset($features['hourly_distribution'])) {
            $lateNightActivity = array_sum(array_slice($features['hourly_distribution'], 22, 2)) +
                                 array_sum(array_slice($features['hourly_distribution'], 0, 6));
            
            if ($lateNightActivity > 10) {
                $anomalies[] = [
                    'type' => 'unusual_hours',
                    'severity' => 'medium',
                    'message' => "Detected {$lateNightActivity} activities during late night hours (10 PM - 6 AM)",
                    'recommendation' => 'Review security protocols for after-hours activities',
                ];
            }
        }

        // Check for sudden spikes
        if (isset($features['booking_frequency'])) {
            $avgFrequency = $features['booking_frequency'];
            $recentFrequency = $this->getRecentFrequency($type, $companyId, 7);
            
            if ($recentFrequency > $avgFrequency * 1.5) {
                $anomalies[] = [
                    'type' => 'demand_spike',
                    'severity' => 'high',
                    'message' => 'Booking demand increased by ' . round((($recentFrequency / $avgFrequency) - 1) * 100) . '% in the last week',
                    'recommendation' => 'Consider allocating additional resources',
                ];
            }
        }

        // Check for low approval rates
        if (isset($features['approval_rate']) && $features['approval_rate'] < 60) {
            $anomalies[] = [
                'type' => 'low_approval_rate',
                'severity' => 'medium',
                'message' => "Approval rate is only {$features['approval_rate']}%",
                'recommendation' => 'Review approval criteria and process efficiency',
            ];
        }

        // Check for unusual patterns from preprocessor
        if (isset($features['unusual_patterns'])) {
            foreach ($features['unusual_patterns'] as $pattern) {
                $anomalies[] = [
                    'type' => $pattern['type'],
                    'severity' => $pattern['severity'],
                    'message' => "Detected {$pattern['count']} instances of {$pattern['type']}",
                    'recommendation' => 'Monitor this pattern for potential security concerns',
                ];
            }
        }

        return $anomalies;
    }

    /**
     * Generate resource optimization recommendations
     */
    public function generateOptimizationRecommendations(int $companyId): array
    {
        $roomFeatures = $this->preprocessor->preprocessRoomBookings($companyId);
        $vehicleFeatures = $this->preprocessor->preprocessVehicleBookings($companyId);
        
        $recommendations = [];

        // Room optimization
        if (!empty($roomFeatures['peak_hours'])) {
            $peakHoursStr = implode(', ', array_map(fn($h) => $h . ':00', $roomFeatures['peak_hours']));
            $recommendations[] = [
                'category' => 'room_scheduling',
                'priority' => 'high',
                'title' => 'Optimize Room Availability During Peak Hours',
                'description' => "Peak booking hours are {$peakHoursStr}. Ensure maximum room availability during these times.",
                'impact' => 'Reduce booking conflicts by up to 30%',
            ];
        }

        if (isset($roomFeatures['approval_rate']) && $roomFeatures['approval_rate'] < 70) {
            $recommendations[] = [
                'category' => 'process_improvement',
                'priority' => 'medium',
                'title' => 'Improve Booking Approval Process',
                'description' => "Current approval rate is {$roomFeatures['approval_rate']}%. Consider streamlining approval workflow.",
                'impact' => 'Increase user satisfaction and reduce processing time',
            ];
        }

        // Vehicle optimization
        if (!empty($vehicleFeatures['vehicle_popularity'])) {
            $mostUsed = count($vehicleFeatures['vehicle_popularity']);
            $recommendations[] = [
                'category' => 'fleet_management',
                'priority' => 'medium',
                'title' => 'Balance Vehicle Utilization',
                'description' => "{$mostUsed} vehicles are heavily utilized. Consider redistributing bookings or adding capacity.",
                'impact' => 'Reduce wait times and improve service availability',
            ];
        }

        // Department usage insights
        if (!empty($roomFeatures['department_usage'])) {
            $topDept = array_key_first($roomFeatures['department_usage']);
            $topCount = $roomFeatures['department_usage'][$topDept];
            $recommendations[] = [
                'category' => 'resource_allocation',
                'priority' => 'low',
                'title' => 'Review Department Resource Allocation',
                'description' => "Department {$topDept} accounts for {$topCount} bookings. Consider dedicated resources.",
                'impact' => 'Improve efficiency for high-usage departments',
            ];
        }

        return $recommendations;
    }

    /**
     * Calculate booking conflict probability
     */
    public function calculateConflictProbability(array $bookingData, int $companyId): array
    {
        $features = $this->preprocessor->preprocessRoomBookings($companyId, 30);
        
        $score = 0;
        $factors = [];

        // Check time slot popularity
        $requestedHour = Carbon::parse($bookingData['start_time'])->hour;
        if (in_array($requestedHour, $features['peak_hours'] ?? [])) {
            $score += 30;
            $factors[] = 'Requested time is during peak hours';
        }

        // Check day of week
        $dayOfWeek = Carbon::parse($bookingData['start_time'])->dayOfWeek;
        $dayDistribution = $features['daily_distribution'] ?? array_fill(0, 7, 0);
        $avgDayBookings = array_sum($dayDistribution) / 7;
        
        if ($dayDistribution[$dayOfWeek] > $avgDayBookings * 1.2) {
            $score += 20;
            $factors[] = 'High booking volume on this day of week';
        }

        // Check lead time
        $leadTime = now()->diffInHours(Carbon::parse($bookingData['start_time']));
        if ($leadTime < 24) {
            $score += 25;
            $factors[] = 'Short notice booking (less than 24 hours)';
        }

        // Check duration
        if (($bookingData['duration_hours'] ?? 0) > 4) {
            $score += 15;
            $factors[] = 'Long duration booking';
        }

        // Check room popularity
        if (isset($bookingData['room_id']) && isset($features['room_popularity'][$bookingData['room_id']])) {
            $score += 10;
            $factors[] = 'Popular room requested';
        }

        return [
            'probability' => min(100, $score),
            'risk_level' => $this->getRiskLevel($score),
            'factors' => $factors,
            'recommendation' => $this->getConflictRecommendation($score),
        ];
    }

    // ==================== HELPER METHODS ====================

    private function calculateTrend(array $timeSeries): float
    {
        if (count($timeSeries) < 2) return 0;
        
        $counts = array_column($timeSeries, 'count');
        $n = count($counts);
        
        // Simple linear regression
        $sumX = array_sum(range(0, $n - 1));
        $sumY = array_sum($counts);
        $sumXY = 0;
        $sumX2 = 0;
        
        for ($i = 0; $i < $n; $i++) {
            $sumXY += $i * $counts[$i];
            $sumX2 += $i * $i;
        }
        
        $slope = ($n * $sumXY - $sumX * $sumY) / ($n * $sumX2 - $sumX * $sumX);
        
        return $slope;
    }

    private function getDayOfWeekAdjustment(array $timeSeries, int $dayOfWeek): float
    {
        $dayData = array_filter($timeSeries, fn($d) => $d['day_of_week'] == $dayOfWeek);
        $allData = $timeSeries;
        
        if (empty($dayData) || empty($allData)) return 1.0;
        
        $dayAvg = array_sum(array_column($dayData, 'count')) / count($dayData);
        $overallAvg = array_sum(array_column($allData, 'count')) / count($allData);
        
        return $overallAvg > 0 ? $dayAvg / $overallAvg : 1.0;
    }

    private function calculateConfidence(array $timeSeries): float
    {
        if (count($timeSeries) < 7) return 0.5;
        if (count($timeSeries) < 30) return 0.7;
        if (count($timeSeries) < 60) return 0.85;
        return 0.95;
    }

    private function getRecentFrequency(string $type, int $companyId, int $days): float
    {
        $timeSeries = $this->preprocessor->createTimeSeriesDataset($type, $companyId, $days);
        $totalCount = array_sum(array_column($timeSeries, 'count'));
        return $totalCount / max($days, 1);
    }

    private function getRiskLevel(int $score): string
    {
        return match(true) {
            $score >= 70 => 'high',
            $score >= 40 => 'medium',
            default => 'low',
        };
    }

    private function getConflictRecommendation(int $score): string
    {
        return match(true) {
            $score >= 70 => 'High conflict risk. Consider alternative time slots or rooms.',
            $score >= 40 => 'Moderate conflict risk. Verify availability before confirming.',
            default => 'Low conflict risk. Booking should proceed smoothly.',
        };
    }

    private function getEmptyForecast(int $days): array
    {
        $forecast = [];
        for ($i = 1; $i <= $days; $i++) {
            $date = now()->addDays($i);
            $forecast[] = [
                'date' => $date->format('Y-m-d'),
                'predicted_count' => 0,
                'confidence' => 0,
                'day_of_week' => $date->format('l'),
            ];
        }
        return $forecast;
    }
}
