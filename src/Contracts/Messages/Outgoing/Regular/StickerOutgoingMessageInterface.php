<?php

namespace SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\Regular;

interface StickerOutgoingMessageInterface extends OutgoingMessageInterface
{
    public function getStickerId(): string;
}
