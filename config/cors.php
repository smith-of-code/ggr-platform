<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie', 'lms-admin/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'https://lms.rosatom-travel.ru',
        'https://main.rosatom-travel.ru',
        'https://rosatom-travel.ru',
    ],

    'allowed_origins_patterns' => [
        '#\bhttps?://.*\.rosatom-travel\.ru\b#',
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
