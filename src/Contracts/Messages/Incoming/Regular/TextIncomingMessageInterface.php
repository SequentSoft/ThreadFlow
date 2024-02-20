<?php

namespace SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular;

interface TextIncomingMessageInterface extends IncomingMessageInterface
{
    public function getText(): string;
}
