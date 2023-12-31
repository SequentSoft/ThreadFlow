<?php

namespace SequentSoft\ThreadFlow\Contracts\Session;

use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;

interface PageStateInterface
{
    /**
     * @param  class-string<PageInterface>|null  $pageClass
     */
    public static function create(
        ?string $pageClass = null,
        array $attributes = [],
    ): PageStateInterface;

    public function getId(): string;

    /**
     * @return class-string<PageInterface>|null
     */
    public function getPageClass(): ?string;

    /**
     * @param  class-string<PageInterface>  $pageClass
     */
    public function setPageClass(string $pageClass): void;

    public function getAttributes(): array;

    public function setAttributes(array $attributes): void;
}
