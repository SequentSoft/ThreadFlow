<?php

namespace SequentSoft\ThreadFlow\Chat;

use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Chat\ParticipantInterface;
use SequentSoft\ThreadFlow\Contracts\Chat\RoomInterface;

class MessageContext implements MessageContextInterface
{
    final public function __construct(
        protected ParticipantInterface $participant,
        protected RoomInterface $room
    ) {
    }

    public function getParticipant(): ParticipantInterface
    {
        return $this->participant;
    }

    public function getRoom(): RoomInterface
    {
        return $this->room;
    }

    public static function createFromIds(
        string $participantId,
        string $roomId
    ): static {
        return new static(
            new Participant($participantId),
            new Room($roomId)
        );
    }
}
