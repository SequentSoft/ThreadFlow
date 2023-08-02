<?php

namespace SequentSoft\ThreadFlow\Contracts\Page;

use Closure;
use SequentSoft\ThreadFlow\Contracts\Session\PageStateInterface;
use SequentSoft\ThreadFlow\Page\PendingDispatchPage;

interface PendingDispatchPageInterface
{
    public function keepAliveCurrentPage(): static;
    public function withBreadcrumbs(): static;
    public function withBreadcrumbsReplace(): static;
    public function dispatch(?PageInterface $contextPage, Closure $callback): PageInterface;
}
