<?php

namespace SequentSoft\ThreadFlow\Chat;

use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Chat\ParticipantInterface;
use SequentSoft\ThreadFlow\Contracts\Chat\RoomInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular\IncomingRegularMessageInterface;

class MessageContext implements MessageContextInterface
{
    final public function __construct(
        protected ParticipantInterface $participant,
        protected RoomInterface $room,
        protected ?ParticipantInterface $forwardFrom = null,
        protected ?IncomingRegularMessageInterface $replyToMessage = null,
    ) {
    }

    public static function createFromIds(
        string $participantId,
        string $roomId,
        ?string $forwardFromId = null,
    ): static {
        return new static(
            new Participant($participantId),
            new Room($roomId),
            $forwardFromId ? new Participant($forwardFromId) : null,
        );
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
