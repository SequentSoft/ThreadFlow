<?php

namespace SequentSoft\ThreadFlow\Contracts\Session;

interface BackgroundPageStatesCollectionInterface
{
    public static function create(array $collection = []): BackgroundPageStatesCollectionInterface;

    public function truncate(int $keepAmount): void;

    public function set(PageStateInterface $pageState): void;

    public function remove(string $id): void;

    public function get(string $id): ?PageStateInterface;

    public function has(string $id): bool;

    public function all(): array;
}
