<?php

namespace SequentSoft\ThreadFlow\Events\Page;

use SequentSoft\ThreadFlow\Contracts\Events\EventInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;

class PageHandleDelegatedEvent implements EventInterface
{
    public function __construct(
        protected PageInterface $fromPage,
        protected PageInterface $toPage,
    ) {
    }

    public function getFromPage(): PageInterface
    {
        return $this->fromPage;
    }

    public function getToPage(): PageInterface
    {
        return $this->toPage;
    }
}
