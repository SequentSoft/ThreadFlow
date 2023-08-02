<?php

namespace SequentSoft\ThreadFlow\Contracts\Session;

interface PageStateInterface
{
    public static function create(
        ?string $pageClass = null,
        array $attributes = [],
    ): PageStateInterface;

    public function getId(): string;

    public function getPageClass(): ?string;

    public function setPageClass(string $pageClass): void;

    public function getAttributes(): array;

    public function setAttributes(array $attributes): void;
}
