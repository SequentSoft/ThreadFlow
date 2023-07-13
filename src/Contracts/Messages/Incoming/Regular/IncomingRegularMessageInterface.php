<?php

namespace SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular;

use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\IncomingMessageInterface;

interface IncomingRegularMessageInterface extends IncomingMessageInterface
{
    public function getText(): string;

    public function setText(string $text);

    public function isText(string $text): bool;
}
