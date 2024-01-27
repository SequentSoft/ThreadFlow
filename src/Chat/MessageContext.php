<?php

namespace SequentSoft\ThreadFlow\Chat;

use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Chat\ParticipantInterface;
use SequentSoft\ThreadFlow\Contracts\Chat\RoomInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular\IncomingRegularMessageInterface;

class MessageContext implements MessageContextInterface
{
    final public function __construct(
        protected string $channelName,
        protected ParticipantInterface $participant,
        protected RoomInterface $room,
        protected ?ParticipantInterface $forwardFrom = null,
        protected ?IncomingRegularMessageInterface $replyToMessage = null,
    ) {
    }

    public static function createFromIds(
        string $channelName,
        string $participantId,
        ?string $roomId = null,
        ?string $forwardFromId = null,
    ): static {
        return new static(
            $channelName,
            new Participant($participantId),
            new Room($roomId ?: $participantId),
            $forwardFromId ? new Participant($forwardFromId) : null,
        );
    }

    public function getChannelName(): string
    {
        return $this->channelName;
    }

    public function getParticipant(): ParticipantInterface
    {
        return $this->participant;
    }

    public function getRoom(): RoomInterface
    {
        return $this->room;
    }

    public function getForwardFrom(): ?ParticipantInterface
    {
        return $this->forwardFrom;
    }

    public function getReplyToMessage(): ?IncomingRegularMessageInterface
    {
        return $this->replyToMessage;
    }
}
