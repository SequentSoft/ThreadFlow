<?php

namespace SequentSoft\ThreadFlow\Contracts\PendingMessages;

use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\Regular\OutgoingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;

interface PendingMessageInterface
{
    public function isOutgoingMessage(): bool;

    public function isPage(): bool;

    public function getMessage(): OutgoingMessageInterface;

    public function getPage(): PageInterface;
}
