<?php

namespace SequentSoft\ThreadFlow\Contracts\Messages\Incoming;

use DateTimeImmutable;
use SequentSoft\ThreadFlow\Contracts\Messages\MessageInterface;

interface CommonIncomingMessageInterface extends MessageInterface
{
    public function getTimestamp(): DateTimeImmutable;

    public function getPageId(): ?string;

    public function setPageId(?string $pageId): static;
}
