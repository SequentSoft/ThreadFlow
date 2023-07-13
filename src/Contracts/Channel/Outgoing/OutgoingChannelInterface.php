<?php

namespace SequentSoft\ThreadFlow\Contracts\Channel\Outgoing;

use SequentSoft\ThreadFlow\Contracts\Config\SimpleConfigInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\OutgoingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;

interface OutgoingChannelInterface
{
    public function config(): SimpleConfigInterface;

    public function send(OutgoingMessageInterface $message, SessionInterface $session): OutgoingMessageInterface;
}
