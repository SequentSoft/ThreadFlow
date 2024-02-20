<?php

return [
    /*
    |------------------------
    | Channels
    |------------------------
    |
    | Here you may define all the channels that ThreadFlow can use to communicate
    | with your users. ThreadFlow supports a variety of great notification services
    | which can all be used here. Supported drivers are: "telegram".
    |
    */
    'channels' => [

        'telegram' => [
            'driver' => 'telegram',
            'session' => env('THREAD_FLOW_SESSION', 'cache'),
            'dispatcher' => 'sync',
            'entry' => \App\ThreadFlow\Pages\IndexPage::class,
            'api_token' => env('TELEGRAM_API_TOKEN', null),
            'webhook_url' => env('TELEGRAM_WEBHOOK_URL', null),
            'webhook_secret' => env('TELEGRAM_WEBHOOK_SECRET', null),
            'timeout' => 30,
            'limit' => 100,
        ],

    ],

    /*
    |------------------------
    | Sessions
    |------------------------
    |
    | Here you may define all the session stores that ThreadFlow can use to store
    | user sessions. ThreadFlow supports a variety of great session stores
    | which can all be used here. Supported drivers are: "cache", "array".
    |
     */
    'sessions' => [
        'database' => [
            'driver' => 'eloquent',
            'model' => \SequentSoft\ThreadFlow\Laravel\Models\ThreadFlowSession::class,
        ],

        'cache' => [
            'driver' => 'cache',
            'store' => env('THREAD_FLOW_SESSION_CACHE_STORE', null),
            'max_lock_seconds' => 10,
            'max_lock_wait_seconds' => 15,
        ],

        'array' => [
            'driver' => 'array',
        ],
    ],

    /*
    |------------------------
    | Dispatchers
    |------------------------
    |
    | Here you may define all the dispatchers that ThreadFlow can use to handle
    | page dispatching. ThreadFlow supports a variety of great dispatchers
    | which can all be used here. Supported drivers are: "sync", "queue".
    |
     */
    'dispatchers' => [
        'sync' => [
            'driver' => 'sync',
        ],
        'queue' => [
            'driver' => 'queue',
            'queue' => env('THREAD_FLOW_QUEUE', 'default'),
        ],
    ],
];
