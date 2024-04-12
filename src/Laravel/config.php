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
            'active_pages' => 'cache',
            'pending_messages' => 'cache',
            'dispatcher' => 'sync',
            'entry' => \App\ThreadFlow\Pages\IndexPage::class,
            'api_token' => env('TELEGRAM_API_TOKEN'),
            'webhook_secret' => env('TELEGRAM_WEBHOOK_SECRET'),
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
    | which can all be used here. Supported drivers are: "cache", "array" and "database".
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

    /*
     |------------------------
     | Active Pages
     |------------------------
     | Here you may define how to store pages that was visited but still active.
     | For example the pages you can go back to or pages for inline keyboards.
     | Supported drivers are: "cache", "array" and "eloquent".
     |
    */
    'active_pages' => [
        'database' => [
            'driver' => 'eloquent',
            'model' => \SequentSoft\ThreadFlow\Laravel\Models\ThreadFlowActivePage::class,
        ],
        'cache' => [
            'driver' => 'cache',
            'store' => env('THREAD_FLOW_ACTIVE_PAGES_CACHE_STORE', null),
        ],
        'array' => [
            'driver' => 'array',
        ],
    ],

    /*
     |------------------------
     | Pending Messages
     |------------------------
     | Here you may define how to store messages that was sent to user but user was busy at the moment.
     | For example the messages that was sent to user but user was filling a form.
     | Supported drivers are: "cache", "array" and "eloquent".
     |
    */
    'pending_messages' => [
        'database' => [
            'driver' => 'eloquent',
            'model' => \SequentSoft\ThreadFlow\Laravel\Models\ThreadFlowPendingMessage::class,
        ],
        'cache' => [
            'driver' => 'cache',
            'store' => env('THREAD_FLOW_PENDING_MESSAGES_CACHE_STORE', null),
        ],
        'array' => [
            'driver' => 'array',
        ],
    ],
];
