<?php

return [
    'channels' => [

        'cli' => [
            'driver' => 'cli',
            'session' => 'array',
            'router' => 'state',
            'dispatcher' => 'sync',
            'entry' => \App\ThreadFlow\Pages\IndexPage::class,
        ],

        'test' => [
            'driver' => 'test',
            'session' => 'array',
            'router' => 'state',
            'dispatcher' => 'sync',
            'entry' => \App\ThreadFlow\Pages\IndexPage::class,
        ],

        'telegram' => [
            'driver' => 'telegram',
            //'session' => 'cache',
            'session' => 'array',
            'router' => 'state',
            'dispatcher' => env('THREAD_FLOW_DISPATCHER', 'sync'),
            'entry' => \App\ThreadFlow\Pages\IndexPage::class,
            'api_token' => env('THREAD_FLOW_TELEGRAM_API_TOKEN', null),
            'webhook_url' => env('THREAD_FLOW_TELEGRAM_WEBHOOK_URL', null),
            'timeout' => 30,
            'limit' => 100,
        ],
    ],
];
