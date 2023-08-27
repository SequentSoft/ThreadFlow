<?php

namespace SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\Service;

use SequentSoft\ThreadFlow\Enums\Messages\TypingType;

interface TypingOutgoingServiceMessageInterface extends OutgoingServiceMessageInterface
{
    public function getType(): TypingType;
}
