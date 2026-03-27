<?php
// app/Services/CheckInValidationService.php

namespace App\Services;

use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CheckInValidationService
{
    /**
     * Validate if appointment can be checked in
     * 
     * @return array ['valid' => bool, 'error' => string|null]
     */
    public function validateCheckIn(Appointment $appointment): array
    {
        // Check 1: Is appointment for today?
        if (!$appointment->appointment_date->isToday()) {
            return [
                'valid' => false,
                'error' => 'This appointment is not for today.'
            ];
        }

        // Check 2: Already checked in?
        if (!$appointment->canCheckIn()) {
            return [
                'valid' => false,
                'error' => 'Patient has already been checked in.'
            ];
        }

        // Check 3: Within operating hours?
        $operatingHoursCheck = $this->validateOperatingHours();
        if (!$operatingHoursCheck['valid']) {
            return $operatingHoursCheck;
        }

        // All checks passed
        return ['valid' => true, 'error' => null];
    }

    /**
     * Validate operating hours (8 AM - 8 PM)
     */
    public function validateOperatingHours(): array
    {
        $currentHour = now()->hour;
        $operatingStart = config('clinic.operating_hours.start', 8);
        $operatingEnd = config('clinic.operating_hours.end', 20);

        if ($currentHour < $operatingStart || $currentHour >= $operatingEnd) {
            return [
                'valid' => false,
                'error' => sprintf(
                    '⏰ Check-in is only available during operating hours (%d:00 AM - %d:00 PM). Current time: %s',
                    $operatingStart,
                    $operatingEnd,
                    now()->format('h:i A')
                )
            ];
        }

        return ['valid' => true, 'error' => null];
    }

    /**
     * Check if current time is within operating hours
     */
    public function isWithinOperatingHours(): bool
    {
        $currentHour = now()->hour;
        $operatingStart = config('clinic.operating_hours.start', 8);
        $operatingEnd = config('clinic.operating_hours.end', 20);

        return $currentHour >= $operatingStart && $currentHour < $operatingEnd;
    }

    /**
     * Get operating hours info
     */
    public function getOperatingHoursInfo(): array
    {
        return [
            'start' => config('clinic.operating_hours.start', 8),
            'end' => config('clinic.operating_hours.end', 20),
            'is_open' => $this->isWithinOperatingHours(),
            'current_time' => now()->format('h:i A'),
        ];
    }
}