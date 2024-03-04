<?php

namespace SequentSoft\ThreadFlow\Laravel\Dispatcher;

use Closure;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\CommonIncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;
use SequentSoft\ThreadFlow\Dispatcher\SyncDispatcher;

class LaravelQueueIncomingDispatcher extends SyncDispatcher
{
    protected static bool $async = true;

    public static function sync(Closure $callback): void
    {
        try {
            static::$async = false;
            $callback();
        } finally {
            static::$async = true;
        }
    }

    public function incoming(
        CommonIncomingMessageInterface $message,
        SessionInterface $session
    ): void {
        if (static::$async) {
            IncomingMessageJob::dispatch(
                $message
            );

            return;
        }

        parent::incoming($message, $session);
    }
}
