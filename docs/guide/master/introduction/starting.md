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

::: tip TIP
You can use the `--timeout` option to specify the timeout in seconds. The default value is 30 seconds.
:::

## Telegram Webhook

To start a bot using a webhook, you need to configure the webhook URL in the Telegram API.
But it requires a web server that have public address, and `APP_URL` in your `.env` file must be set to the public address of your application.

```sh [artisan]
$ php artisan threadflow:telegram-webhook-set --channel=my-telegram-channel
```
After that, telegram will send all messages to your application using the webhook.
