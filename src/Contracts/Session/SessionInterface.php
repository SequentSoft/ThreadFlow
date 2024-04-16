<?php

namespace SequentSoft\ThreadFlow\Contracts\Session;

use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;

interface SessionInterface
{
    public function getId(): string;

    public function getData(): SessionDataInterface;

    public function getCurrentPage(): ?PageInterface;

    public function setCurrentPage(?PageInterface $currentPage): void;

    public function delete(string $key): void;

    public function get(string $key, mixed $default = null): mixed;

    public function set(string $key, mixed $data): void;

    public function reset(): void;

    public function toArray(): array;
}
