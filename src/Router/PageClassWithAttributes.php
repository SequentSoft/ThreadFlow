<?php

namespace SequentSoft\ThreadFlow\Router;

class PageClassWithAttributes
{
    public function __construct(
        protected string $pageClass,
        protected array $attributes,
        protected bool $isFallback,
    ) {}

    public function getPageClass(): string
    {
        return $this->pageClass;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function isFallback(): bool
    {
        return $this->isFallback;
    }
}
