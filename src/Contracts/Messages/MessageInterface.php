<?php

namespace SequentSoft\ThreadFlow\Contracts\Messages;

use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;

interface MessageInterface
{
    public function setContext(?MessageContextInterface $context): static;
    public function setId(?string $id): static;
    public function getId(): ?string;
    public function getContext(): ?MessageContextInterface;
}
