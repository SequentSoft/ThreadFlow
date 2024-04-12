<?php

namespace SequentSoft\ThreadFlow\Builders;

use Closure;
use SequentSoft\ThreadFlow\Chat\MessageContext;
use SequentSoft\ThreadFlow\Chat\Participant;
use SequentSoft\ThreadFlow\Chat\Room;
use SequentSoft\ThreadFlow\Contracts\Channel\ChannelInterface;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Chat\ParticipantInterface;
use SequentSoft\ThreadFlow\Contracts\Chat\RoomInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\BasicOutgoingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;

class ChannelPendingSend
{
    public function __construct(
        protected ChannelInterface $channel,
        protected Closure $makeTextMessageCallback,
        protected ?ParticipantInterface $participant = null,
        protected ?RoomInterface $room = null,
    ) {
    }

    public function withParticipant(ParticipantInterface|string $participant): static
    {
        $this->participant = is_string($participant)
            ? new Participant($participant)
            : $participant;

        return $this;
    }

    public function getParticipant(): ?ParticipantInterface
    {
        return $this->participant;
    }

    public function withRoom(RoomInterface|string $room): static
    {
        $this->room = is_string($room)
            ? new Room($room)
            : $room;

        return $this;
    }

    public function getRoom(): ?RoomInterface
    {
        return $this->room;
    }

    protected function createMessageContext(): MessageContextInterface
    {
        return MessageContext::createFromIds(
            channelName: $this->channel->getName(),
            participantId: $this->participant?->getId() ?? $this->room?->getId(),
            roomId: $this->room?->getId(),
        );
    }

    public function showPage(PageInterface $page): void
    {
        $this->channel->dispatchTo($this->createMessageContext(), $page);
    }

    public function sendMessage(
        string|BasicOutgoingMessageInterface $message,
    ): ?BasicOutgoingMessageInterface {
        $context = $this->createMessageContext();

        if (is_string($message)) {
            $message = call_user_func($this->makeTextMessageCallback, $message, $context);
        }

        return $this->channel->dispatchTo($context, $message);
    }
}
