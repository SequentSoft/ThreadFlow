# Starting a bot

How to start a bot depends on the driver you are using.

::: info INFO
More information about drivers you can find [Drivers Page](/guide/master/drivers/). 
:::

For example, if you are using the `Telegram driver`, you can use two ways to start the bot: long polling and webhook.

## Telegram Long Polling

Long polling is the simplest way to start a bot. You just need to run the command:

```sh [artisan]
$ php artisan threadflow:telegram-polling --channel=my-telegram-channel
```
You can use it without any additional configuration.
It's the best way to start a bot for testing purposes and local development.

**Now your bot will start and listen for incoming messages.**

::: warning WARNING
If you change the code of the bot, you need to restart the bot to apply the changes.
`Ctrl+C` to stop the bot and run the command again.
:::

::: tip TIP
You can use the `--timeout` option to specify the timeout in seconds. The default value is 30 seconds.
:::

## Telegram Webhook

The webhook method is the best way to start a bot in production. It's more efficient than long polling.

### Webhook route setup

First, you need to set up a route to handle incoming messages from Telegram.
You can change the route to any other route you want.

```php
Route::post(
    '/thread-flow/webhook/telegram',
    \SequentSoft\ThreadFlowTelegram\Laravel\Controllers\WebhookHandleController::class
)->name('threadflow.telegram.webhook');
```

Also, you can add `TELEGRAM_WEBHOOK_SECRET` to your `.env` file to verify the incoming requests from Telegram.

```
TELEGRAM_WEBHOOK_SECRET="random-string-to-verify-webhook-requests"
```

To start a bot using a webhook, you need to configure the webhook URL in the Telegram API.
But it requires a web server that have public address, and `APP_URL` in your `.env` file must be set to the public address of your application.

```sh [artisan]
$ php artisan threadflow:telegram-webhook-set --channel=my-telegram-channel
```
After that, telegram will send all messages to your application using the webhook.
