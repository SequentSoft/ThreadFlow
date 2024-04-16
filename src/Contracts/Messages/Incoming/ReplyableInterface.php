<?php

namespace SequentSoft\ThreadFlow\Contracts\Messages\Incoming;

use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular\IncomingMessageInterface;

interface ReplyableInterface
{
    public function getRepliedMessage(): ?IncomingMessageInterface;
}
