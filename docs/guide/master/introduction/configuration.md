# Configuration

After installation, you can publish the configuration file using the following command:

```sh [artisan]
$ php artisan vendor:publish --provider="SequentSoft\ThreadFlow\Laravel\ServiceProvider"
```
This command will create a `thread-flow.php` configuration file in the `config` directory of your Laravel application.

::: details Example of the contents of this file
```php
'channels' => [
    'my-telegram-channel' => [
        'driver' => 'telegram',
        'session' => env('THREAD_FLOW_SESSION', 'database'),
        'dispatcher' => env('THREAD_FLOW_DISPATCHER', 'sync'),
        'entry' => \App\ThreadFlow\Pages\IndexPage::class,
        'api_token' => env('TELEGRAM_API_TOKEN', null),
        'webhook_url' => env('TELEGRAM_WEBHOOK_URL', null),
        'webhook_secret' => env('TELEGRAM_WEBHOOK_SECRET', null),
        'timeout' => 30,
        'limit' => 100,
    ],
],

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
    ]
],


'dispatchers' => [
    'sync' => [
        'driver' => 'sync',
    ],
    'queue' => [
        'driver' => 'queue',
        'queue' => env('THREAD_FLOW_QUEUE', 'default'),
    ],
],
```
:::

## Channels

Contains a list of all channels that you want to use in your application.
Each channel has a unique key that you can use to reference it in your application.

You can use any key you want, and it does not have to match the name of the channel.
For example, you can use the `my-telegram-channel` key to reference the Telegram channel in your application.

## Driver

The `driver` option specifies the driver that should be used for the channel.
Yoy can use all installed drivers. For example, you can use the `telegram` driver for the Telegram channel.

::: info INFO
More information about drivers you can find [Drivers Page](/guide/master/drivers/). 
:::

## Session

The `session` option specifies the session store that should be used for the channel.
Available session stores: `database`, `cache`, `array` or any custom configured session store.

::: info INFO
More information about sessions you can find [Sessions Page](/guide/master/advanced/sessions).
:::


## Dispatcher

The `dispatcher` option specifies the dispatcher that should be used for the channel.
Available dispatchers: `sync` and `queue`.

::: info INFO
More information about dispatchers you can find [Dispatchers Page](/guide/master/advanced/dispatchers).
:::


## Entry

The `entry` option specifies the entry page that should be used for the channel.
This is the first page that will be displayed to the user when he starts the conversation.

## Other settings

Other settings are specific to the driver you are using.
For example, for the Telegram driver, you can use the `api_token`, `webhook_url`, `webhook_secret`, `timeout` and `limit` options.
