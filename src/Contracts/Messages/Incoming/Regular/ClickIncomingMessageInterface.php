<?php

namespace SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular;

use SequentSoft\ThreadFlow\Contracts\Keyboard\ButtonInterface;

interface ClickIncomingMessageInterface extends IncomingMessageInterface
{
    public function getKey(): string;

    public function getButton(): ButtonInterface;
}
