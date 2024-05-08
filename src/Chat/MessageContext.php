<?php

namespace SequentSoft\ThreadFlow\Chat;

use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Chat\ParticipantInterface;
use SequentSoft\ThreadFlow\Contracts\Chat\RoomInterface;
use SequentSoft\ThreadFlow\Traits\HasUserResolver;

class MessageContext implements MessageContextInterface
{
    use HasUserResolver;

    final public function __construct(
        protected string $channelName,
        protected ParticipantInterface $participant,
        protected RoomInterface $room
    ) {
    }

    public function getUser(): mixed
    {
        return $this->userResolver
            ? call_user_func($this->userResolver, $this)
            : null;
    }

    public static function createFromIds(string $channelName, string $participantId, ?string $roomId = null): static
    {
        return new static(
            $channelName,
            new Participant($participantId),
            new Room($roomId ?: $participantId),
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

    public function asKey(): string
    {
        $channelName = $this->channelName;
        $roomId = $this->room->getId();
        $participantId = $this->participant->getId();

        return "{$channelName}:{$roomId}:{$participantId}";
    }

    public function __serialize(): array
    {
        return [
            'channel' => $this->channelName,
            'participant' => $this->participant,
            'room' => $this->room,
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->channelName = $data['channel'];
        $this->participant = $data['participant'];
        $this->room = $data['room'];
    }
}
