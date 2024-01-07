<?php

namespace SequentSoft\ThreadFlow\Contracts\Session;

interface SessionInterface
{
    public function getData(): SessionDataInterface;

    public function getPageState(): PageStateInterface;

    public function setPageState(PageStateInterface $pageState): void;

    public function getBackgroundPageStates(): BackgroundPageStatesCollectionInterface;

    public function getBreadcrumbs(): BreadcrumbsCollectionInterface;

    public function delete(string $key): void;

    public function get(string $key, mixed $default = null): mixed;

    public function set(string $key, mixed $data): void;

    public function reset(): void;
}
