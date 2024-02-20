<?php

namespace SequentSoft\ThreadFlow\Messages\Incoming\Regular;

use DateTimeImmutable;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular\ContactIncomingMessageInterface;

class ContactIncomingMessage extends IncomingMessage implements ContactIncomingMessageInterface
{
    final public function __construct(
        string $id,
        ?MessageContextInterface $context,
        DateTimeImmutable $timestamp,
        protected string $phoneNumber,
        protected string $firstName,
        protected string $lastName,
        protected string $userId,
    ) {
        parent::__construct($id, $context, $timestamp);

        $this->setText($phoneNumber);
    }

    public static function make(
        string $phoneNumber,
        string $firstName = '',
        string $lastName = '',
        string $userId = '',
        ?string $id = null,
        ?MessageContextInterface $context = null,
        ?DateTimeImmutable $timestamp = null,
    ): static {
        return new static(
            id: $id ?? static::generateId(),
            context: $context,
            timestamp: $timestamp ?? new DateTimeImmutable(),
            phoneNumber: $phoneNumber,
            firstName: $firstName,
            lastName: $lastName,
            userId: $userId,
        );
    }

    public function getPhoneNumber(): string
    {
        return $this->phoneNumber;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }
}
