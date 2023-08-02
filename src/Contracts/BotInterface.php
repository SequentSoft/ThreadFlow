<?php

namespace SequentSoft\ThreadFlow\Contracts;

use Closure;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Config\ConfigInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\IncomingMessageInterface;

interface BotInterface
{
    public function showPage(
        string $channelName,
        MessageContextInterface|string $context,
        string $pageClass,
        array $pageAttributes = []
    ): void;

    public function process(
        string $channelName,
        IncomingMessageInterface $message,
        ?Closure $incomingCallback = null,
        ?Closure $outgoingCallback = null,
    ): void;

    public function incoming(string $channelName, Closure $callback): void;

    public function outgoing(string $channelName, Closure $callback): void;

    public function getChannelConfig(string $channelName): ConfigInterface;

    public function getAvailableChannels(): array;
}
