<?php

namespace SequentSoft\ThreadFlow\Contracts\Chat;

use Closure;

interface MessageContextInterface
{
    public function getChannelName(): string;

    public function getParticipant(): ParticipantInterface;

    public function getRoom(): RoomInterface;

    public function getUser(): mixed;

    public function setUserResolver(?Closure $userResolver): void;

    public function getUserResolver(): ?Closure;

    public function asKey(): string;
}
