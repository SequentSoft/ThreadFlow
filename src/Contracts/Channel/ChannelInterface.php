<?php

namespace SequentSoft\ThreadFlow\Contracts\Channel;

use Closure;
use SequentSoft\ThreadFlow\Channel\Builders\ChannelPendingSend;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Chat\ParticipantInterface;
use SequentSoft\ThreadFlow\Contracts\Chat\RoomInterface;
use SequentSoft\ThreadFlow\Contracts\Config\ConfigInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\BasicIncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\BasicOutgoingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;
use SequentSoft\ThreadFlow\Testing\PendingTestInput;

interface ChannelInterface
{
    public function setUserResolver(?Closure $userResolver): void;

    public function getName(): string;

    public function on(string $event, callable $callback): void;

    public function getConfig(): ConfigInterface;

    public function incoming(BasicIncomingMessageInterface $message): void;

    public function forParticipant(string|ParticipantInterface $participant): ChannelPendingSend;

    public function forRoom(string|RoomInterface $room): ChannelPendingSend;

    public function forContext(MessageContextInterface $context): ChannelPendingSend;

    public function dispatchTo(
        MessageContextInterface $context,
        PageInterface|BasicOutgoingMessageInterface $pageOrMessage,
        bool $force = false,
    ): ?BasicOutgoingMessageInterface;

    public function registerExceptionHandler(Closure $callback): void;

    public function disableExceptionsHandlers(): void;

    public function test(): PendingTestInput;
}
