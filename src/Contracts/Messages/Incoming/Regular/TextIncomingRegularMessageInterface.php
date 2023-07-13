<?php

namespace SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular;

interface TextIncomingRegularMessageInterface extends IncomingRegularMessageInterface
{
    public function getText(): string;
}
