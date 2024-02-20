<?php

namespace SequentSoft\ThreadFlow\Contracts\Messages\Outgoing;

use SequentSoft\ThreadFlow\Contracts\Messages\MessageInterface;

interface CommonOutgoingMessageInterface extends MessageInterface
{
    public function reply(): static;

    public function update(): static;
}
