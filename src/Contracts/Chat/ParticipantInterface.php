<?php

namespace SequentSoft\ThreadFlow\Contracts\Chat;

interface ParticipantInterface
{
    public function getId(): string;

    public function getFirstName(): ?string;

    public function getLastName(): ?string;

    public function getUsername(): ?string;

    public function getPhotoUrl(): ?string;

    public function getLanguage(): ?string;
}
