<?php

namespace SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular;

interface ContactIncomingMessageInterface extends IncomingMessageInterface
{
    public function getPhoneNumber(): string;

    public function getFirstName(): string;

    public function getLastName(): string;

    public function getUserId(): string;
}
