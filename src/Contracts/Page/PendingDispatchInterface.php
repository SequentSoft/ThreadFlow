<?php

namespace SequentSoft\ThreadFlow\Contracts\Page;

use Closure;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\IncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Session\PageStateInterface;
use SequentSoft\ThreadFlow\Page\PendingDispatchPage;

interface PendingDispatchInterface
{
    public function dispatch(?PageInterface $contextPage, Closure $callback): PageInterface;
}
