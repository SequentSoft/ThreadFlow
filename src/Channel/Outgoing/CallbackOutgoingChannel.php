<?php

namespace SequentSoft\ThreadFlow\Channel\Outgoing;

use Closure;
use SequentSoft\ThreadFlow\Contracts\Channel\Outgoing\OutgoingChannelInterface;
use SequentSoft\ThreadFlow\Contracts\Config\SimpleConfigInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\OutgoingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;

class CallbackOutgoingChannel implements OutgoingChannelInterface
{
    public function __construct(
        protected SimpleConfigInterface $config,
        protected ?Closure $callback = null,
    ) {
    }

    public function setCallback(Closure $callback): void
    {
        $this->callback = $callback;
    }

    public function send(
        OutgoingMessageInterface $message,
        SessionInterface $session,
        ?PageInterface $contextPage = null
    ): OutgoingMessageInterface {
        if ($this->callback !== null) {
            return call_user_func($this->callback, $message, $session, $contextPage);
        }

        return $message;
    }
}
