<?php

namespace SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\Regular;

interface StickerOutgoingRegularMessageInterface extends OutgoingRegularMessageInterface
{
    public function getStickerId(): string;
}
