<?php

namespace SequentSoft\ThreadFlow\Events\Bot;

use SequentSoft\ThreadFlow\Contracts\Events\EventInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;

class SessionClosedEvent implements EventInterface
{
    public function __construct(
        protected SessionInterface $session
    ) {
    }

    public function getSession(): SessionInterface
    {
        return $this->session;
    }
}
