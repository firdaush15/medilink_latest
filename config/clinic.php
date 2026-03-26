<?php
// config/clinic.php

return [
    'operating_hours' => [
        'start' => env('CLINIC_OPERATING_START', 0),  // 8 AM
        'end' => env('CLINIC_OPERATING_END', 24),     // 8 PM
        'timezone' => env('CLINIC_TIMEZONE', 'Asia/Kuala_Lumpur'),
    ],

    'check_in' => [
        'grace_period_minutes' => env('CHECK_IN_GRACE_PERIOD', 15),
        'allow_early_checkin_minutes' => env('ALLOW_EARLY_CHECKIN', 30),
    ],

    'queue' => [
        'max_queue_size' => env('MAX_QUEUE_SIZE', 50),
        'avg_consultation_minutes' => env('AVG_CONSULTATION_MINUTES', 20),
    ],
];