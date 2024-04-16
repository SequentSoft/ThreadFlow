<?php

namespace SequentSoft\ThreadFlow\Messages\Incoming;

use DateTimeImmutable;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\BasicIncomingMessageInterface;
use SequentSoft\ThreadFlow\Messages\Message;
use SequentSoft\ThreadFlow\Traits\GenerateUniqueIdsTrait;

abstract class BasicIncomingMessage extends Message implements BasicIncomingMessageInterface
{
    use GenerateUniqueIdsTrait;

    protected ?string $pageId = null;

    public function __construct(
        string $id,
        ?MessageContextInterface $context,
        protected DateTimeImmutable $timestamp
    ) {
        $this->setId($id);
        $this->setContext($context);
    }

    public static function generateId(): string
    {
        return static::generateUniqueId();
    }

    public function getPageId(): ?string
    {
        return $this->pageId;
    }

    public function setPageId(?string $pageId): static
    {
        $this->pageId = $pageId;

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
}
