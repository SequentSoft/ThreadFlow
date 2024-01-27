<?php

namespace SequentSoft\ThreadFlow\Builders;

use SequentSoft\ThreadFlow\Chat\MessageContext;
use SequentSoft\ThreadFlow\Chat\Participant;
use SequentSoft\ThreadFlow\Chat\Room;
use SequentSoft\ThreadFlow\Contracts\Channel\ChannelInterface;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Chat\ParticipantInterface;
use SequentSoft\ThreadFlow\Contracts\Chat\RoomInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\OutgoingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PendingDispatchPageInterface;

class ChannelPendingSend
{
    public function __construct(
        protected ChannelInterface $channel,
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

    public function showPage(
        PendingDispatchPageInterface|string $page,
        array $pageAttributes = []
    ): void {
        $this->channel->showPage($this->createMessageContext(), $page, $pageAttributes);
    }

    public function sendMessage(
        string|OutgoingMessageInterface $message,
    ): OutgoingMessageInterface {
        return $this->channel->sendMessage($this->createMessageContext(), $message);
    }
}
