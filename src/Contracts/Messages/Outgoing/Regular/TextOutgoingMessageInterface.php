<?php

namespace SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\Regular;

interface TextOutgoingMessageInterface extends OutgoingMessageInterface
{
    public function getText(): string;
}
