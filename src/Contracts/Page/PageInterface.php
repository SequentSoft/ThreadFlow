<?php

namespace SequentSoft\ThreadFlow\Contracts\Page;

use Closure;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Session\PageStateInterface;

interface PageInterface
{
    public function getChannelName(): string;

    public function getMessageContext(): MessageContextInterface;

    public function isBackground(): bool;

    public function getState(): PageStateInterface;

    public function execute(Closure $callback): ?PendingDispatchPageInterface;
}
