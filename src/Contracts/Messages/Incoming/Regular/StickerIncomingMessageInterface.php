<?php

namespace SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular;

interface StickerIncomingMessageInterface extends IncomingMessageInterface
{
    public function getStickerId(): string;

    public function getName(): string;

    public function getEmoji(): string;
}
