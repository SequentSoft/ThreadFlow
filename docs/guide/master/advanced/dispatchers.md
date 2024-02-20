# Dispatchers

The `dispatcher` option specifies the dispatcher that should be used for the channel.
Available dispatchers: `sync` and `queue`.

## Sync

With the `sync` dispatcher, all messages will be handled synchronously.
In most cases, you should use the `sync` dispatcher its usually faster response time and easier to debug.

## Queue

With the `queue` dispatcher, all messages will be handled asynchronously using Laravel's queue system.
