<?php

namespace SequentSoft\ThreadFlow\Events\Message;

use SequentSoft\ThreadFlow\Contracts\Events\EventInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\BasicIncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;

class IncomingMessageDispatchingEvent implements EventInterface
{
    public function __construct(
        protected BasicIncomingMessageInterface $message,
        protected PageInterface $page,
        protected SessionInterface $session,
    ) {
    }

    public function getMessage(): BasicIncomingMessageInterface
    {
        return $this->message;
    }

    public function getSession(): SessionInterface
    {
        return $this->session;
    }

    public function getPage(): PageInterface
    {
        return $this->page;
    }
}
