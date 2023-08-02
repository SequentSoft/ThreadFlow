<?php

namespace SequentSoft\ThreadFlow\Session;

use SequentSoft\ThreadFlow\Contracts\Session\BackgroundPageStatesCollectionInterface;
use SequentSoft\ThreadFlow\Contracts\Session\PageStateInterface;

class BackgroundPageStatesCollection implements BackgroundPageStatesCollectionInterface
{
    final public function __construct(
        protected array $collection = []
    ) {
    }

    public static function create(array $collection = []): BackgroundPageStatesCollectionInterface
    {
        return new static($collection);
    }

    public function truncate(int $keepAmount): void
    {
        $this->collection = array_slice($this->collection, -$keepAmount, $keepAmount, true);
    }

    public function set(PageStateInterface $pageState): void
    {
        unset($this->collection[$pageState->getId()]);
        $this->collection[$pageState->getId()] = $pageState;
    }

    public function remove(string $id): void
    {
        unset($this->collection[$id]);
    }

    public function has(string $id): bool
    {
        return $this->get($id) !== null;
    }

    public function get(string $id): ?PageStateInterface
    {
        return $this->collection[$id] ?? null;
    }

    public function all(): array
    {
        return $this->collection;
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
