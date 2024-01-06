<?php

namespace SequentSoft\ThreadFlow\Page;

use SequentSoft\ThreadFlow\Contracts\Page\PendingDispatchPageInterface;
use SequentSoft\ThreadFlow\Contracts\Session\PageStateInterface;
use SequentSoft\ThreadFlow\Enums\State\BreadcrumbsType;

class PendingDispatchPage implements PendingDispatchPageInterface
{
    protected bool $keepAliveContextPage = false;

    protected BreadcrumbsType $breadcrumbsType = BreadcrumbsType::None;

    public function __construct(
        protected string $page,
        protected array $attributes,
        protected ?string $stateId = null,
    ) {
    }

    public static function fromState(PageStateInterface $state): static
    {
        return new static(
            $state->getPageClass(),
            $state->getAttributes(),
            $state->getId(),
        );
    }

    public function appendAttributes(array $attributes): static
    {
        $this->attributes = array_merge($this->attributes, $attributes);
        return $this;
    }

    public function withStateId(string $stateId): static
    {
        $this->stateId = $stateId;
        return $this;
    }

    public function getStateId(): ?string
    {
        return $this->stateId;
    }

    public function keepAliveCurrentPage(): static
    {
        $this->keepAliveContextPage = true;
        return $this;
    }

    public function withBreadcrumbs(): static
    {
        $this->breadcrumbsType = BreadcrumbsType::Append;
        return $this;
    }

    public function withBreadcrumbsReplace(): static
    {
        $this->breadcrumbsType = BreadcrumbsType::Replace;
        return $this;
    }

    public function getBreadcrumbsType(): BreadcrumbsType
    {
        return $this->breadcrumbsType;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getPage(): string
    {
        return $this->page;
    }

    public function isKeepAliveContextPage(): bool
    {
        return $this->keepAliveContextPage;
    }
}
