<?php

namespace SequentSoft\ThreadFlow\Contracts\Channel;

use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Config\ConfigInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\IncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\OutgoingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PendingDispatchPageInterface;

interface ChannelInterface
{
    public function on(string $event, callable $callback): void;

    public function getConfig(): ConfigInterface;

    public function incoming(IncomingMessageInterface $message): void;

    public function showPage(
        MessageContextInterface|string $context,
        PendingDispatchPageInterface|string $page,
        array $pageAttributes = []
    ): void;

    public function sendMessage(
        MessageContextInterface|string $context,
        OutgoingMessageInterface|string $message,
    ): OutgoingMessageInterface;
}
