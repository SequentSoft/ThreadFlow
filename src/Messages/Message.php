<?php

namespace SequentSoft\ThreadFlow\Messages;

use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\MessageInterface;

abstract class Message implements MessageInterface
{
    protected ?string $id = null;

    protected ?MessageContextInterface $context = null;

    public function setContext(?MessageContextInterface $context): static
    {
        $this->context = $context;

        return $this;
    }

    public function setId(?string $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getContext(): ?MessageContextInterface
    {
        return $this->context;
    }
}
