<?php

namespace SequentSoft\ThreadFlow\Contracts\Page;

use Closure;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\IncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Session\PageStateInterface;
use SequentSoft\ThreadFlow\Page\PendingDispatchPage;

interface PendingDispatchPageInterface extends PendingDispatchInterface
{
    public function withMessage(?IncomingMessageInterface $message): static;
    public function keepAliveCurrentPage(): static;
    public function withBreadcrumbs(): static;
    public function withBreadcrumbsReplace(): static;
}
