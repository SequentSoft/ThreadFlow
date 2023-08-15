<?php

namespace SequentSoft\ThreadFlow\Events\Page;

use SequentSoft\ThreadFlow\Contracts\Events\EventInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular\IncomingRegularMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PendingDispatchPageInterface;

class PageHandleRegularMessageEvent implements EventInterface
{
    public function __construct(
        protected PageInterface $page,
        protected IncomingRegularMessageInterface $message,
    ) {
    }

    public function getPage(): PageInterface
    {
        return $this->page;
    }

    public function getMessage(): IncomingRegularMessageInterface
    {
        return $this->message;
    }
}
