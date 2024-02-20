<?php

namespace SequentSoft\ThreadFlow\Events\Page;

use SequentSoft\ThreadFlow\Contracts\Events\EventInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Service\IncomingServiceMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;

class PageHandleServiceMessageEvent implements EventInterface
{
    public function __construct(
        protected PageInterface                   $page,
        protected IncomingServiceMessageInterface $message,
    ) {
    }

    public function getPage(): PageInterface
    {
        return $this->page;
    }

    public function getMessage(): IncomingServiceMessageInterface
    {
        return $this->message;
    }
}
