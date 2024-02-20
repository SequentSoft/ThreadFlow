<?php

namespace SequentSoft\ThreadFlow\Contracts\Session;

use Closure;
use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;

interface SessionInterface
{
    public function setUserResolver(?Closure $userResolver): void;

    public function pushPendingInteraction(mixed $interaction): void;

    public function takePendingInteraction(): mixed;

    public function hasPendingInteractions(): bool;

    public function getData(): SessionDataInterface;

    public function getServiceData(): SessionDataInterface;

    public function getCurrentPage(): ?PageInterface;

    public function setCurrentPage(?PageInterface $currentPage): void;

    public function delete(string $key): void;

    public function get(string $key, mixed $default = null): mixed;

    public function set(string $key, mixed $data): void;

    public function reset(): void;

    public function toArray(): array;

    public static function fromArray(array $data): SessionInterface;
}
