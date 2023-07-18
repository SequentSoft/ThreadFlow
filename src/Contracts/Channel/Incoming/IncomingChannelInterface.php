<?php

namespace SequentSoft\ThreadFlow\Contracts\Channel\Incoming;

use Closure;
use SequentSoft\ThreadFlow\Contracts\Config\SimpleConfigInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\IncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;
use SequentSoft\ThreadFlow\Contracts\DataFetchers\DataFetcherInterface;

interface IncomingChannelInterface
{
    /**
     * @param DataFetcherInterface $fetcher
     * @param Closure(IncomingMessageInterface $message): void $callback
     */
    public function listen(DataFetcherInterface $fetcher, Closure $callback): void;

    public function preprocess(IncomingMessageInterface $message, SessionInterface $session): IncomingMessageInterface;

    public function getConfig(): SimpleConfigInterface;
}
