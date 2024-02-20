<?php

namespace SequentSoft\ThreadFlow\Messages\Incoming;

use DateTimeImmutable;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\CommonIncomingMessageInterface;
use SequentSoft\ThreadFlow\Messages\Message;

abstract class CommonIncomingMessage extends Message implements CommonIncomingMessageInterface
{
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
        return md5(uniqid('', true));
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
