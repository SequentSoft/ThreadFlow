<?php

namespace SequentSoft\ThreadFlow\Contracts\Session;

interface BreadcrumbsCollectionInterface
{
    public static function create(array $collection = []): BreadcrumbsCollectionInterface;

    public function push(PageStateInterface $pageState): void;

    public function pop(): ?PageStateInterface;

    public function clear(): void;

    public function all(): array;
}
