<?php

namespace App\Services\AI;

use App\Models\AISettings;
use Carbon\Carbon;

/**
 * AI Prediction Service
 *
 * Uses preprocessed data to make predictions and generate insights.
 * All numeric thresholds are read from the ai_settings database table —
 * no hardcoded magic numbers in this class.
 */
class PredictionService
{
    private DataPreprocessor $preprocessor;

    public function __construct()
    {
        $this->preprocessor = new DataPreprocessor();
    }

    // ── Public API ────────────────────────────────────────────────────────────

    /**
     * Predict future booking demand using LSTM or fallback methods.
     */
    public function predictBookingDemand(string $type, int $companyId, int $forecastDays = 7): array
    {
        $timeSeries = $this->preprocessor->createTimeSeriesDataset($type, $companyId, 90);

        if (empty($timeSeries)) {
            return $this->getEmptyForecast($forecastDays);
        }

        $lstmClient = new LSTMClient();
        $result     = $lstmClient->predictWithFallback($timeSeries, $forecastDays);

        // Read fallback bounds from DB in case LSTM was unavailable and the
        // fallback model didn't already embed them.
        $lowerMult = AISettings::get('ma_lower_bound', 0.8);
        $upperMult = AISettings::get('ma_upper_bound', 1.2);

        $forecast = [];
        foreach ($result['predictions'] as $prediction) {
            $forecast[] = [
                'date'            => $prediction['date'],
                'predicted_count' => (int) $prediction['predicted'],
                'lower_bound'     => (int) ($prediction['lower_bound']  ?? $prediction['predicted'] * $lowerMult),
                'upper_bound'     => (int) ($prediction['upper_bound']  ?? $prediction['predicted'] * $upperMult),
                'confidence'      => $prediction['confidence'] ?? AISettings::get('ma_confidence', 0.60),
                'day_of_week'     => date('l', strtotime($prediction['date'])),
                'method'          => $result['method'],  // 'lstm' or 'fallback'
            ];
        }

        return $forecast;
    }

    /**
     * Detect anomalies in booking patterns.
     */
    public function detectAnomalies(string $type, int $companyId): array
    {
        $features = match ($type) {
            'room_booking'    => $this->preprocessor->preprocessRoomBookings($companyId),
            'vehicle_booking' => $this->preprocessor->preprocessVehicleBookings($companyId),
            'guestbook'       => $this->preprocessor->preprocessGuestbookData($companyId),
            'delivery'        => $this->preprocessor->preprocessDeliveryData($companyId),
            default           => [],
        };

        $anomalies = [];

        // ── Late-night activity ───────────────────────────────────────────────
        if (isset($features['hourly_distribution'])) {
            $lateNightActivity =
                array_sum(array_slice($features['hourly_distribution'], 22, 2)) +
                array_sum(array_slice($features['hourly_distribution'],  0, 6));

            // Threshold: any late-night activity beyond 10 events is flagged.
            // This is a security heuristic, not a tunable AI param, so 10 is
            // intentional and documented here rather than DB-driven.
            if ($lateNightActivity > 10) {
                $anomalies[] = [
                    'type'           => 'unusual_hours',
                    'severity'       => 'medium',
                    'message'        => "Detected {$lateNightActivity} activities during late night hours (10 PM – 6 AM)",
                    'recommendation' => 'Review security protocols for after-hours activities',
                ];
            }
        }

        // ── Demand spike ─────────────────────────────────────────────────────
        if (isset($features['booking_frequency'])) {
            $avgFrequency    = $features['booking_frequency'];
            $recentFrequency = $this->getRecentFrequency($type, $companyId, 7);

            // Spike multiplier: read from DB (defaults to 1.5 = 50% above average)
            $spikeMultiplier = (float) AISettings::get('demand_spike_multiplier', 1.5);

            if ($avgFrequency > 0 && $recentFrequency > $avgFrequency * $spikeMultiplier) {
                $anomalies[] = [
                    'type'           => 'demand_spike',
                    'severity'       => 'high',
                    'message'        => 'Booking demand increased by '
                        . round((($recentFrequency / $avgFrequency) - 1) * 100)
                        . '% in the last week',
                    'recommendation' => 'Consider allocating additional resources',
                ];
            }
        }

        // ── Low approval rate ─────────────────────────────────────────────────
        if (isset($features['approval_rate'])) {
            $lowApprovalThreshold = (float) AISettings::get('low_approval_threshold', 60);

            if ($features['approval_rate'] < $lowApprovalThreshold) {
                $anomalies[] = [
                    'type'           => 'low_approval_rate',
                    'severity'       => 'medium',
                    'message'        => "Approval rate is only {$features['approval_rate']}%",
                    'recommendation' => 'Review approval criteria and process efficiency',
                ];
            }
        }

        // ── Unusual patterns from preprocessor ───────────────────────────────
        if (isset($features['unusual_patterns'])) {
            foreach ($features['unusual_patterns'] as $pattern) {
                $anomalies[] = [
                    'type'           => $pattern['type'],
                    'severity'       => $pattern['severity'],
                    'message'        => "Detected {$pattern['count']} instances of {$pattern['type']}",
                    'recommendation' => 'Monitor this pattern for potential security concerns',
                ];
            }
        }

        return $anomalies;
    }

    /**
     * Generate resource optimisation recommendations.
     */
    public function generateOptimizationRecommendations(int $companyId): array
    {
        $roomFeatures    = $this->preprocessor->preprocessRoomBookings($companyId);
        $vehicleFeatures = $this->preprocessor->preprocessVehicleBookings($companyId);

        $recommendations = [];

        // Room optimisation
        if (!empty($roomFeatures['peak_hours'])) {
            $peakHoursStr    = implode(', ', array_map(fn ($h) => $h . ':00', $roomFeatures['peak_hours']));
            $recommendations[] = [
                'category'    => 'room_scheduling',
                'priority'    => 'high',
                'title'       => 'Optimize Room Availability During Peak Hours',
                'description' => "Peak booking hours are {$peakHoursStr}. Ensure maximum room availability during these times.",
                'impact'      => 'Reduce booking conflicts by up to 30%',
            ];
        }

        $approvalImprovementThreshold = (float) AISettings::get('approval_improvement_threshold', 70);
        if (isset($roomFeatures['approval_rate']) && $roomFeatures['approval_rate'] < $approvalImprovementThreshold) {
            $recommendations[] = [
                'category'    => 'process_improvement',
                'priority'    => 'medium',
                'title'       => 'Improve Booking Approval Process',
                'description' => "Current approval rate is {$roomFeatures['approval_rate']}%. Consider streamlining approval workflow.",
                'impact'      => 'Increase user satisfaction and reduce processing time',
            ];
        }

        // Vehicle optimisation
        if (!empty($vehicleFeatures['vehicle_popularity'])) {
            $mostUsed        = count($vehicleFeatures['vehicle_popularity']);
            $recommendations[] = [
                'category'    => 'fleet_management',
                'priority'    => 'medium',
                'title'       => 'Balance Vehicle Utilization',
                'description' => "{$mostUsed} vehicles are heavily utilized. Consider redistributing bookings or adding capacity.",
                'impact'      => 'Reduce wait times and improve service availability',
            ];
        }

        // Department usage insights
        if (!empty($roomFeatures['department_usage'])) {
            $topDept         = array_key_first($roomFeatures['department_usage']);
            $topCount        = $roomFeatures['department_usage'][$topDept];
            $recommendations[] = [
                'category'    => 'resource_allocation',
                'priority'    => 'low',
                'title'       => 'Review Department Resource Allocation',
                'description' => "Department {$topDept} accounts for {$topCount} bookings. Consider dedicated resources.",
                'impact'      => 'Improve efficiency for high-usage departments',
            ];
        }

        return $recommendations;
    }

    /**
     * Calculate booking conflict probability.
     */
    public function calculateConflictProbability(array $bookingData, int $companyId): array
    {
        $features = $this->preprocessor->preprocessRoomBookings($companyId, 30);

        // Read all score weights from DB
        $scorePeakHour    = (int) AISettings::get('conflict_score_peak_hour',    30);
        $scoreHighDayVol  = (int) AISettings::get('conflict_score_high_day_vol', 20);
        $scoreShortNotice = (int) AISettings::get('conflict_score_short_notice', 25);
        $scoreLongDur     = (int) AISettings::get('conflict_score_long_duration', 15);
        $scorePopularRoom = (int) AISettings::get('conflict_score_popular_room',  10);

        $highThreshold    = (int) AISettings::get('risk_high_threshold', 70);
        $medThreshold     = (int) AISettings::get('risk_med_threshold',  40);
        $urgencyHours     = (int) AISettings::get('urgency_hours',       24);
        $longDuration     = (int) AISettings::get('long_duration_hours',  4);
        $dayVolumeRatio   = (float) AISettings::get('conflict_day_volume_ratio', 1.2);

        $score   = 0;
        $factors = [];

        // Peak hour check
        $requestedHour = Carbon::parse($bookingData['start_time'])->hour;
        if (in_array($requestedHour, $features['peak_hours'] ?? [])) {
            $score   += $scorePeakHour;
            $factors[] = 'Requested time is during peak hours';
        }

        // Day-of-week volume check
        $dayOfWeek       = Carbon::parse($bookingData['start_time'])->dayOfWeek;
        $dayDistribution = $features['daily_distribution'] ?? array_fill(0, 7, 0);
        $avgDayBookings  = array_sum($dayDistribution) / 7;

        if ($dayDistribution[$dayOfWeek] > $avgDayBookings * $dayVolumeRatio) {
            $score   += $scoreHighDayVol;
            $factors[] = 'High booking volume on this day of week';
        }

        // Short-notice check
        $leadTime = now()->diffInHours(Carbon::parse($bookingData['start_time']));
        if ($leadTime < $urgencyHours) {
            $score   += $scoreShortNotice;
            $factors[] = "Short notice booking (less than {$urgencyHours} hours)";
        }

        // Duration check
        if (($bookingData['duration_hours'] ?? 0) > $longDuration) {
            $score   += $scoreLongDur;
            $factors[] = "Long duration booking (>{$longDuration}h)";
        }

        // Popular room check
        if (isset($bookingData['room_id'], $features['room_popularity'][$bookingData['room_id']])) {
            $score   += $scorePopularRoom;
            $factors[] = 'Popular room requested';
        }

        return [
            'probability'    => min(100, $score),
            'risk_level'     => $this->getRiskLevel($score, $highThreshold, $medThreshold),
            'factors'        => $factors,
            'recommendation' => $this->getConflictRecommendation($score, $highThreshold, $medThreshold),
        ];
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    /**
     * Simple linear regression over a time-series count array.
     * Returns the slope (trend per day).
     */
    private function calculateTrend(array $timeSeries): float
    {
        if (count($timeSeries) < 2) return 0.0;

        $counts = array_column($timeSeries, 'count');
        $n      = count($counts);
        $sumX   = array_sum(range(0, $n - 1));
        $sumY   = array_sum($counts);
        $sumXY  = 0;
        $sumX2  = 0;

        for ($i = 0; $i < $n; $i++) {
            $sumXY += $i * $counts[$i];
            $sumX2 += $i * $i;
        }

        $denom = $n * $sumX2 - $sumX * $sumX;
        return $denom !== 0 ? ($n * $sumXY - $sumX * $sumY) / $denom : 0.0;
    }

    private function getRecentFrequency(string $type, int $companyId, int $days): float
    {
        $timeSeries = $this->preprocessor->createTimeSeriesDataset($type, $companyId, $days);
        $totalCount = array_sum(array_column($timeSeries, 'count'));
        return $totalCount / max($days, 1);
    }

    private function getRiskLevel(int $score, int $high, int $med): string
    {
        return match (true) {
            $score >= $high => 'high',
            $score >= $med  => 'medium',
            default         => 'low',
        };
    }

    private function getConflictRecommendation(int $score, int $high, int $med): string
    {
        return match (true) {
            $score >= $high => 'High conflict risk. Consider alternative time slots or rooms.',
            $score >= $med  => 'Moderate conflict risk. Verify availability before confirming.',
            default         => 'Low conflict risk. Booking should proceed smoothly.',
        };
    }

    private function getEmptyForecast(int $days): array
    {
        $forecast = [];
        for ($i = 1; $i <= $days; $i++) {
            $date       = now()->addDays($i);
            $forecast[] = [
                'date'            => $date->format('Y-m-d'),
                'predicted_count' => 0,
                'confidence'      => 0,
                'day_of_week'     => $date->format('l'),
            ];
        }
        return $forecast;
    }
}
