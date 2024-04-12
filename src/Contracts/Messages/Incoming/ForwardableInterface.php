<?php

namespace SequentSoft\ThreadFlow\Contracts\Messages\Incoming;

use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;

interface ForwardableInterface
{
    public function getForwardedFrom(): ?MessageContextInterface;
}
