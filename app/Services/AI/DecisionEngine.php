<?php

namespace App\Services\AI;

use Carbon\Carbon;

class DecisionEngine
{
    public function evaluate($booking)
    {
        $score = 0;
        $reasons = [];

        // Conflict Detection
        if ($this->hasConflict($booking)) {
            $score += 40;
            $reasons[] = "Schedule conflict detected";
        }

        // Urgency
        $hours = Carbon::now()->diffInHours($booking->start_time);
        if ($hours < 24) {
            $score += 20;
            $reasons[] = "High urgency booking";
        }

        // Duration
        if ($booking->duration_hours > 4) {
            $score += 20;
            $reasons[] = "Long duration booking";
        }

        // User Priority
        $priority = match($booking->user->role ?? 'user') {
            'Admin' => 1,
            'Manager' => 0.8,
            default => 0.5
        };

        $score += $priority * 20;

        return [
            'score' => $score,
            'label' => $this->label($score),
            'reasons' => $reasons
        ];
    }

    private function hasConflict($booking)
    {
        return $booking->conflict ?? false;
    }

    private function label($score)
    {
        return match(true) {
            $score >= 70 => 'High Risk',
            $score >= 40 => 'Moderate Risk',
            default => 'Low Risk'
        };
    }
}