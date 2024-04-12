<?php

namespace SequentSoft\ThreadFlow\Page\Responses;

use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular\IncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;

class AnswerToPage
{
    public function __construct(
        protected PageInterface $page,
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
