<?php

declare(strict_types=1);

return [
    'api' => [
        'version' => 'v1',
        'trace_header' => 'X-Trace-Id',
    ],

    'cache' => [
        'default_ttl' => 300,
        'prefix' => env('CACHE_PREFIX', 'beyond_mrp'),
    ],

    'queue' => [
        'connection' => env('QUEUE_CONNECTION', 'redis'),
        'retry_after' => 90,
    ],

    'logging' => [
        'channel' => env('LOG_CHANNEL', 'stack'),
        'sql_channel' => env('LOG_SQL_CHANNEL', 'daily'),
    ],
];
