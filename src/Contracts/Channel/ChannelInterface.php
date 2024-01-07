<?php

namespace SequentSoft\ThreadFlow\Contracts\Channel;

use Closure;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Config\ConfigInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\IncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\OutgoingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PendingDispatchPageInterface;
use SequentSoft\ThreadFlow\Contracts\Session\PageStateInterface;
use SequentSoft\ThreadFlow\Testing\ResultsRecorder;

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

    public function registerExceptionHandler(Closure $callback): void;

    public function disableExceptionsHandlers(): void;

    public function fakeMessageContext(): MessageContextInterface;

    public function testInput(
        string|IncomingMessageInterface $message,
        string|PageStateInterface|null $state = null,
        array $sessionAttributes = [],
    ): ResultsRecorder;
}
