<?php

namespace SequentSoft\ThreadFlow\Messages\Incoming\Regular;

use DateTimeImmutable;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Forms\FormInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular\FormResultMessageInterface;

class FormResultIncomingMessage extends IncomingMessage implements FormResultMessageInterface
{
    final public function __construct(
        string $id,
        ?MessageContextInterface $context,
        DateTimeImmutable $timestamp,
        protected FormInterface $form,
    ) {
        parent::__construct($id, $context, $timestamp);
    }

    public static function make(
        FormInterface $form,
        ?string $id = null,
        ?MessageContextInterface $context = null,
        ?DateTimeImmutable $timestamp = null,
    ): static {
        return new static(
            $id ?? static::generateId(),
            $context,
            $timestamp ?? new DateTimeImmutable(),
            $form,
        );
    }

    public function getForm(): FormInterface
    {
        return $this->form;
    }
}
