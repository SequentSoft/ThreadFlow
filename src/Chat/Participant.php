<?php

namespace SequentSoft\ThreadFlow\Chat;

use SequentSoft\ThreadFlow\Contracts\Chat\ParticipantInterface;

class Participant implements ParticipantInterface
{
    protected ?string $firstName = null;
    protected ?string $lastName = null;
    protected ?string $language = null;
    protected ?string $username = null;
    protected ?string $photoUrl = null;

    public function __construct(
        protected string $id,
    ) {}

    public function getId(): string
    {
        return $this->id;
    }

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function getPhotoUrl(): ?string
    {
        return $this->photoUrl;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function setLanguage(?string $language): self
    {
        $this->language = $language;
        return $this;
    }

    public function setUsername(?string $username): self
    {
        $this->username = $username;
        return $this;
    }

    public function setPhotoUrl(?string $photoUrl): self
    {
        $this->photoUrl = $photoUrl;
        return $this;
    }
}
