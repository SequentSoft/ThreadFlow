# Telegram Driver

## Install

To use the Telegram driver, you must first install the driver package via Composer:

```sh [composer]
$ composer require sequentsoft/threadflow-telegram
```

## Obtain Your Telegram Bot Token

::: info INFO
Official documentation: [https://core.telegram.org/bots/tutorial#getting-ready](https://core.telegram.org/bots/tutorial#getting-ready)
:::

Each bot has a unique token which can also be revoked at any time via [@BotFather](https://t.me/botfather).

Obtaining a token is as simple as contacting [@BotFather](https://t.me/botfather), issuing the `/newbot` command and following the steps until you're given a new token. You can find a step-by-step guide [here](https://core.telegram.org/bots/features#creating-a-new-bot).


Your token will look something like this:

```
4839574812:AAFD39kkdpWt3ywyRZergyOLMaJhac60qc
```
> Make sure to save your token in a secure place, treat it like a password and <strong>don't share it with anyone</strong>.


## Configuration

The corresponding parameters for the driver must be configured in the `.env` file.

```
TELEGRAM_API_TOKEN="your-token-here"

# In case you are using the webhook method
TELEGRAM_WEBHOOK_URL="/my-app/webhook"
TELEGRAM_WEBHOOK_SECRET="random string to verify webhook requests"
```

## Artisan Commands

The driver provides several Artisan commands to help you manage your bot.

### Long Polling

Long polling is the simplest way to start a bot. You just need to run the command:

```sh [artisan]
$ php artisan threadflow:telegram-polling --channel=my-telegram-channel
```

### Set Webhook

To set the webhook URL for your bot, use the `threadflow:telegram-webhook-set` command:

```sh [artisan]
$ php artisan threadflow:telegram-webhook-set --channel=my-telegram-channel
```

### Remove Webhook

To remove the webhook URL for your bot, use the `threadflow:telegram-webhook-remove` command:

```sh [artisan]
$ php artisan threadflow:telegram-webhook-remove --channel=my-telegram-channel
```

### Get Webhook Info

To get the webhook info for your bot, use the `threadflow:telegram-webhook-info` command:

```sh [artisan]
$ php artisan threadflow:telegram-webhook-info --channel=my-telegram-channel
```

## Outgoing Messages

You can use telegram specific outgoing message classes to send messages to the user with additional features.

### Text Messages

```php
use SequentSoft\ThreadFlowTelegram\Messages\Outgoing\Regular\TelegramTextOutgoingMessage;
```

This class has additional methods:

#### withHtmlParseMode(): TelegramTextOutgoingMessage

Sets the parse mode to HTML. You can use this method to send messages with HTML formatting.

#### withMarkdownParseMode(): TelegramTextOutgoingMessage

Sets the parse mode to Markdown. You can use this method to send messages with Markdown formatting.
