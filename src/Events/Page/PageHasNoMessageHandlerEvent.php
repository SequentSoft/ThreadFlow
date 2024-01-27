<?php

namespace SequentSoft\ThreadFlow\Events\Page;

use SequentSoft\ThreadFlow\Contracts\Events\EventInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;

class PageHasNoMessageHandlerEvent implements EventInterface
{
    public function __construct(
        protected PageInterface $page
    ) {
    }

    public function getPage(): PageInterface
    {
        return $this->page;
    }
}
