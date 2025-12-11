<?php

// config/news.php

use App\Services\NewsProviders\GuardianProvider;
use App\Services\NewsProviders\NewsApiProvider;
use App\Services\NewsProviders\NyTimesProvider;

return [
    'providers' => [
        'newsapi' => [
            'base_url' => 'https://newsapi.org/v2/',
            'key' => env('NEWS_API_KEY'),
            'class' => NewsApiProvider::class,
            'timeout' => 30,
            'rate_limit' => [
                'requests' => 100,      // Max requests
                'per_seconds' => 3600,  // Per time window (1 hour)
            ],
        ],

        'guardian' => [
            'base_url' => 'https://content.guardianapis.com/',
            'class' => GuardianProvider::class,
            'key' => env('GUARDIAN_API_KEY'),
            'timeout' => 30,
            'rate_limit' => [
                'requests' => 500,
                'per_seconds' => 3600, // 1 hour
            ],
        ],

        'nytimes' => [
            'base_url' => 'https://api.nytimes.com/',
            'class' => NyTimesProvider::class,
            'key' => env('NY_TIMES_API_KEY'),
            'timeout' => 30,
            'rate_limit' => [
                'requests' => 500,
                'per_seconds' => 86400, // 24 hours
            ],
        ],
    ],
];
