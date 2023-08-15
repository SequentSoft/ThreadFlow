<?php

namespace SequentSoft\ThreadFlow\Events\Page;

use SequentSoft\ThreadFlow\Contracts\Events\EventInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PendingDispatchPageInterface;

class PageDispatchedEvent implements EventInterface
{
    public function __construct(
        protected PendingDispatchPageInterface $pendingDispatchPage,
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

    public function getPendingDispatchPage(): PendingDispatchPageInterface
    {
        return $this->pendingDispatchPage;
    }
}
