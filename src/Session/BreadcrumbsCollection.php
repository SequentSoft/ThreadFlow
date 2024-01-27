<?php

namespace SequentSoft\ThreadFlow\Session;

use SequentSoft\ThreadFlow\Contracts\Session\BreadcrumbsCollectionInterface;
use SequentSoft\ThreadFlow\Contracts\Session\PageStateInterface;

class BreadcrumbsCollection implements BreadcrumbsCollectionInterface
{
    final public function __construct(
        protected array $collection = []
    ) {
    }

    public static function create(array $collection = []): BreadcrumbsCollectionInterface
    {
        return new static($collection);
    }

    public function push(PageStateInterface $pageState): void
    {
        $this->collection[] = $pageState;
    }

    public function pop(): ?PageStateInterface
    {
        return array_pop($this->collection);
    }

    public function all(): array
    {
        return $this->collection;
    }

    public function clear(): void
    {
        $this->collection = [];
    }

    public function __serialize(): array
    {
        return $this->collection;
    }

    public function __unserialize(array $collection): void
    {
        $this->collection = $collection;
    }
}
