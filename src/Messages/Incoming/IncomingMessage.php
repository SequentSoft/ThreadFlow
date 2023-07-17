<?php

namespace SequentSoft\ThreadFlow\Messages\Incoming;

use DateTimeImmutable;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\IncomingMessageInterface;
use SequentSoft\ThreadFlow\Messages\Message;

abstract class IncomingMessage extends Message implements IncomingMessageInterface
{
    protected ?array $raw;

    public function __construct(
        string $id,
        MessageContextInterface $context,
        protected DateTimeImmutable $timestamp
    ) {
        $this->setId($id);
        $this->setContext($context);
    }

    public function getRaw(): ?array
    {
        return $this->raw;
    }

    public function setRaw(array $raw): static
    {
        $this->raw = $raw;

        return $this;
    }

    public function getTimestamp(): DateTimeImmutable
    {
        return $this->timestamp;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getContext(): MessageContextInterface
    {
        return $this->context;
    }

    public function ignore(): IgnoreIncomingMessage
    {
        return new IgnoreIncomingMessage($this->getId(), $this->getContext(), $this->getTimestamp());
    }
}
