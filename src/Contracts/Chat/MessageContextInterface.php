<?php

namespace SequentSoft\ThreadFlow\Contracts\Chat;

interface MessageContextInterface
{
    public function getChannelName(): string;

    public function getParticipant(): ParticipantInterface;

    public function getRoom(): RoomInterface;
}
