# Installation

## Prerequisites

- PHP version 8.1 or higher.
- [Laravel](https://laravel.com/) version 9 or higher.

## Library Installation
Install the library via Composer:

```sh [composer]
$ composer require sequentsoft/threadflow
```

## Driver Installation
Also install the driver for the messaging platform you want to use. All drivers are listed on the [ThreadFlow Drivers page](/guide/master/drivers/).

For example, to use the Telegram driver:
```sh [composer]
$ composer require sequentsoft/threadflow-telegram
```

::: tip NOTE
When you are using the Telegram driver, you must create a Telegram bot and get the `API token`.
You can find more information about this in the [Telegram driver documentation](/guide/master/drivers/telegram#obtain-your-telegram-bot-token).
:::

The corresponding parameters for the driver must be configured in the `.env` file.
```
TELEGRAM_API_TOKEN="your-token-here"
TELEGRAM_WEBHOOK_URL="/my-app/webhook"
TELEGRAM_WEBHOOK_SECRET="random string to verify webhook requests"
```
More information about the parameters can be found in the [documentation for the driver](/guide/master/drivers/telegram).
