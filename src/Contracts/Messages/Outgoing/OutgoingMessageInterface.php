<?php

namespace SequentSoft\ThreadFlow\Contracts\Messages\Outgoing;

use SequentSoft\ThreadFlow\Contracts\Messages\MessageInterface;

interface OutgoingMessageInterface extends MessageInterface
{
    public function reply(): static;

    public function update(): static;
}
