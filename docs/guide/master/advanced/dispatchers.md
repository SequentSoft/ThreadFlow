# Dispatchers

The `dispatcher` option specifies the dispatcher that should be used for the channel.
Available dispatchers: `sync` and `queue`.

## Sync

With the `sync` dispatcher, all messages will be handled synchronously.
It is useful for local development, testing, AWS Lambda etc.

## Queue

With the `queue` dispatcher, all messages will be handled asynchronously.
