<?php

namespace SequentSoft\ThreadFlow\Contracts\Page;

use SequentSoft\ThreadFlow\Contracts\Session\PageStateInterface;
use SequentSoft\ThreadFlow\Enums\State\BreadcrumbsType;

interface PendingDispatchPageInterface
{
    public function keepAliveCurrentPage(): static;

    public function withBreadcrumbs(): static;

    public function withBreadcrumbsReplace(): static;

    public function getBreadcrumbsType(): BreadcrumbsType;

    public function getAttributes(): array;

    public function getPage(): string;

    public function isKeepAliveContextPage(): bool;

    public function withStateId(string $stateId): static;

    public function getStateId(): ?string;

    public function appendAttributes(array $attributes): static;

    public static function fromState(PageStateInterface $state): static;
}
