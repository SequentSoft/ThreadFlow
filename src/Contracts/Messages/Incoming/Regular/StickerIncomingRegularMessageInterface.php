<?php

namespace SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular;

interface StickerIncomingRegularMessageInterface extends IncomingRegularMessageInterface
{
    public function getStickerId(): string;
}
