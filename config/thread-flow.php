<?php

return [
    /*
    |------------------------
    | ThreadFlow Channels
    |------------------------
    |
    | Configure the bot's channels. Each channel is wrapped with
    | its own settings. Available drivers: 'cli', 'test', 'telegram'.
    |
    | 'session': how session data is stored ('array' or 'cache').
    |
    | 'dispatcher': how the bot's messages are dispatched ('sync' or 'queue').
    |
    | 'entry': the page class that will be opened when the bot starts.
    |
    | Telegram-specific settings: 'api_token', 'webhook_url', 'timeout', 'limit'.
    |
    */
    'channels' => [

        'cli' => [
            'driver' => 'cli',
            'session' => 'array',
            'entry' => \App\ThreadFlow\Pages\IndexPage::class,
        ],

        'test' => [
            'driver' => 'test',
            'entry' => \App\ThreadFlow\Pages\IndexPage::class,
        ],

        'telegram' => [
            'driver' => 'telegram',
            'session' => 'cache', // cache or array
            'dispatcher' => env('THREAD_FLOW_DISPATCHER', 'sync'), // sync or queue
            'entry' => \App\ThreadFlow\Pages\IndexPage::class,
            'api_token' => env('THREAD_FLOW_TELEGRAM_API_TOKEN', null),
            'webhook_url' => env('THREAD_FLOW_TELEGRAM_WEBHOOK_URL', null),
            'timeout' => 30,
            'limit' => 100,
        ],
    ],
];
