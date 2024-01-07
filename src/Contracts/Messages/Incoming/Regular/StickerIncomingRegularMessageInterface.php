<?php

namespace SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular;

interface StickerIncomingRegularMessageInterface extends IncomingRegularMessageInterface
{
    public function getStickerId(): string;

    public function getName(): string;

    public function getEmoji(): string;
}
