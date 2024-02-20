<?php

namespace SequentSoft\ThreadFlow\Events\Page;

use SequentSoft\ThreadFlow\Contracts\Events\EventInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular\IncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;

class PageHandleRegularMessageEvent implements EventInterface
{
    public function __construct(
        protected PageInterface            $page,
        protected IncomingMessageInterface $message,
    ) {
    }

    public function getPage(): PageInterface
    {
        return $this->page;
    }

    public function getMessage(): IncomingMessageInterface
    {
        return $this->message;
    }
}
