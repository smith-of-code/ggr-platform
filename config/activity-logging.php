<?php

return [

    'enabled' => env('ACTIVITY_LOG_ENABLED', true),

    'log_exceptions' => env('ACTIVITY_LOG_EXCEPTIONS', true),

    'log_422' => env('ACTIVITY_LOG_422', false),

    'excluded_paths' => [
        'up',
        'horizon/*',
        'livewire/*',
    ],

    'retention_days' => (int) env('ACTIVITY_LOG_RETENTION_DAYS', 90),

];
