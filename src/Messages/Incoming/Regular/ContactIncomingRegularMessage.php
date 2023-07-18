<?php

namespace SequentSoft\ThreadFlow\Messages\Incoming\Regular;

use DateTimeImmutable;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular\ContactIncomingRegularMessageInterface;

class ContactIncomingRegularMessage extends IncomingRegularMessage implements ContactIncomingRegularMessageInterface
{
    final public function __construct(
        string $id,
        MessageContextInterface $context,
        DateTimeImmutable $timestamp,
        protected string $phoneNumber,
        protected string $firstName,
        protected string $lastName,
        protected string $userId,
    ) {
        parent::__construct($id, $context, $timestamp);

        $this->setText($phoneNumber);
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
