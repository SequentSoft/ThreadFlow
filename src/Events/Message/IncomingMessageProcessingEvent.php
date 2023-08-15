<?php

namespace SequentSoft\ThreadFlow\Events\Message;

use SequentSoft\ThreadFlow\Contracts\Events\EventInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\IncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Session\PageStateInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;

class IncomingMessageProcessingEvent implements EventInterface
{
    public function __construct(
        protected PageStateInterface $pageState,
        protected IncomingMessageInterface $message,
        protected SessionInterface $session,
    ) {
    }

    public function getPageState(): PageStateInterface
    {
        return $this->pageState;
    }

    public function getMessage(): IncomingMessageInterface
    {
        return $this->message;
    }

    public function getSession(): SessionInterface
    {
        return $this->session;
    }
}
