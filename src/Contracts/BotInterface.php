<?php

namespace SequentSoft\ThreadFlow\Contracts;

use Closure;
use SequentSoft\ThreadFlow\Contracts\Channel\Incoming\IncomingChannelInterface;
use SequentSoft\ThreadFlow\Contracts\Channel\Outgoing\OutgoingChannelInterface;
use SequentSoft\ThreadFlow\Contracts\Config\ConfigInterface;
use SequentSoft\ThreadFlow\Contracts\Dispatcher\DispatcherInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\IncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionStoreInterface;

interface BotInterface
{
    public function process(
        string $channelName,
        IncomingMessageInterface $message,
        ?Closure $incomingCallback = null,
        ?Closure $outgoingCallback = null,
    ): void;

    public function incoming(string $channelName, Closure $callback): void;

    public function outgoing(string $channelName, Closure $callback): void;

    public function getChannelConfig(string $channelName): ConfigInterface;
}
