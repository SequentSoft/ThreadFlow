<?php

namespace SequentSoft\ThreadFlow\Messages\Incoming\Regular;

use DateTimeImmutable;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular\TextIncomingMessageInterface;
use Stringable;

class TextIncomingMessage extends IncomingMessage implements TextIncomingMessageInterface, Stringable
{
    final public function __construct(
        string $id,
        ?MessageContextInterface $context,
        DateTimeImmutable $timestamp,
        string $text,
    ) {
        parent::__construct($id, $context, $timestamp);

        $this->setText($text);
    }

    public static function make(
        ?string $text = null,
        ?string $id = null,
        ?MessageContextInterface $context = null,
        ?DateTimeImmutable $timestamp = null,
    ): static {
        return new static(
            $id ?? static::generateId(),
            $context,
            $timestamp ?? new DateTimeImmutable(),
            $text ?? '',
        );
    }

    public function __toString()
    {
        return $this->getText();
    }
}
