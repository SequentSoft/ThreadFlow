<?php

namespace SequentSoft\ThreadFlow\Messages\Incoming\Regular;

use DateTimeImmutable;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Keyboard\ButtonInterface;
use SequentSoft\ThreadFlow\Contracts\Keyboard\Buttons\BackButtonInterface;
use SequentSoft\ThreadFlow\Contracts\Keyboard\Buttons\TextButtonInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular\ClickIncomingMessageInterface;

class ClickIncomingMessage extends IncomingMessage implements ClickIncomingMessageInterface
{
    final public function __construct(
        string $id,
        ?MessageContextInterface $context,
        DateTimeImmutable $timestamp,
        protected ButtonInterface $button,
    ) {
        parent::__construct($id, $context, $timestamp);

        $this->setText($button->getTitle());
    }

    public static function make(
        ButtonInterface $button,
        ?string $id = null,
        ?MessageContextInterface $context = null,
        ?DateTimeImmutable $timestamp = null,
    ): static {
        return new static(
            $id ?? static::generateId(),
            $context,
            $timestamp ?? new DateTimeImmutable(),
            $button,
        );
    }

    public function getKey(): string
    {
        if ($this->button instanceof TextButtonInterface) {
            return $this->button->getCallbackData();
        }

        if ($this->button instanceof BackButtonInterface) {
            return $this->button->getCallbackData();
        }

        return '';
    }

    public function getButton(): ButtonInterface
    {
        return $this->button;
    }
}
