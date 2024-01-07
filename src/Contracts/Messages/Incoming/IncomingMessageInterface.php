<?php

namespace SequentSoft\ThreadFlow\Contracts\Messages\Incoming;

use DateTimeImmutable;
use SequentSoft\ThreadFlow\Contracts\Messages\MessageInterface;

interface IncomingMessageInterface extends MessageInterface
{
    public function getRaw(): ?array;

    public function getTimestamp(): DateTimeImmutable;

    public function getStateId(): ?string;

    public function setStateId(?string $stateId): static;
}
