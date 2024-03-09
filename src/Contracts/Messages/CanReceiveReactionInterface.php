<?php

namespace SequentSoft\ThreadFlow\Contracts\Messages;

interface CanReceiveReactionInterface
{
    public function addReaction(string $reaction): self;
}
