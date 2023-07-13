<?php

namespace SequentSoft\ThreadFlow\Chat;

use SequentSoft\ThreadFlow\Contracts\Chat\RoomInterface;

class Room implements RoomInterface
{
    protected ?string $name = null;
    protected ?string $type = null;
    protected ?string $description = null;
    protected ?int $participantCount = null;
    protected ?string $photoUrl = null;

    public function __construct(
        protected string $id,
    ) {}

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getParticipantCount(): ?int
    {
        return $this->participantCount;
    }

    public function getPhotoUrl(): ?string
    {
        return $this->photoUrl;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function setParticipantCount(?int $participantCount): self
    {
        $this->participantCount = $participantCount;
        return $this;
    }

    public function setPhotoUrl(?string $photoUrl): self
    {
        $this->photoUrl = $photoUrl;
        return $this;
    }
}
