<?php

namespace SequentSoft\ThreadFlow\Events\Page;

use SequentSoft\ThreadFlow\Contracts\Events\EventInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;

class PageDispatchingEvent implements EventInterface
{
    public function __construct(
        protected PageInterface $page,
        protected ?PageInterface $contextPage = null,
    ) {
    }

    public function getPage(): PageInterface
    {
        return $this->page;
    }

    public function getContextPage(): ?PageInterface
    {
        return $this->contextPage;
    }
}
