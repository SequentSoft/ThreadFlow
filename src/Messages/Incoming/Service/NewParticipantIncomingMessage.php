<?php

namespace SequentSoft\ThreadFlow\Messages\Incoming\Service;

use DateTimeImmutable;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Service\NewParticipantIncomingMessageInterface;

class NewParticipantIncomingMessage extends IncomingServiceMessage implements NewParticipantIncomingMessageInterface
{
    final public function __construct(
        string $id,
        ?MessageContextInterface $context,
        DateTimeImmutable $timestamp,
    ) {
        parent::__construct($id, $context, $timestamp);
    }

    public static function make(
        ?string $id = null,
        ?MessageContextInterface $context = null,
        ?DateTimeImmutable $timestamp = null,
    ): static {
        return new static(
            $id ?? static::generateId(),
            $context,
            $timestamp ?? new DateTimeImmutable(),
        );
    }
}
