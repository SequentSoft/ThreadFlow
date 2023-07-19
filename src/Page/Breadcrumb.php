<?php

namespace SequentSoft\ThreadFlow\Page;

use Closure;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\IncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Router\RouterInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;

class Breadcrumb
{
    public function __construct(
        protected string $pageClass,
        protected array $attributes,
    ) {
    }

    public function getPageClass(): string
    {
        return $this->pageClass;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }
}
