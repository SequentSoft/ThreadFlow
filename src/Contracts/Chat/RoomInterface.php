<?php

namespace SequentSoft\ThreadFlow\Contracts\Chat;

interface RoomInterface
{
    public function getId(): string;

    public function getName(): ?string;

    public function getType(): ?string;

    public function getDescription(): ?string;

    public function getParticipantCount(): ?int;

    public function getPhotoUrl(): ?string;
}
