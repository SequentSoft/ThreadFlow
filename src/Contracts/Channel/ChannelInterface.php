<?php

namespace SequentSoft\ThreadFlow\Contracts\Channel;

use Closure;
use SequentSoft\ThreadFlow\Builders\ChannelPendingSend;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Chat\ParticipantInterface;
use SequentSoft\ThreadFlow\Contracts\Chat\RoomInterface;
use SequentSoft\ThreadFlow\Contracts\Config\ConfigInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\CommonIncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\CommonOutgoingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;
use SequentSoft\ThreadFlow\Testing\PendingTestInput;

interface ChannelInterface
{
    public function setUserResolver(?Closure $userResolver): void;

    public function getName(): string;

    public function on(string $event, callable $callback): void;

    public function getConfig(): ConfigInterface;

    public function incoming(CommonIncomingMessageInterface $message): void;

    public function forParticipant(string|ParticipantInterface $participant): ChannelPendingSend;

    public function forRoom(string|RoomInterface $room): ChannelPendingSend;

    public function dispatchTo(
        MessageContextInterface                      $context,
        PageInterface|CommonOutgoingMessageInterface $pageOrMessage,
    ): ?CommonOutgoingMessageInterface;

    public function registerExceptionHandler(Closure $callback): void;

    public function disableExceptionsHandlers(): void;

    public function test(): PendingTestInput;
}
