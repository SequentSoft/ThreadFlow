<?php

namespace SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular;

interface ContactIncomingRegularMessageInterface extends IncomingRegularMessageInterface
{
    public function getPhoneNumber(): string;
    public function getFirstName(): string;
    public function getLastName(): string;
    public function getUserId(): string;
}
