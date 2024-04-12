<?php

namespace SequentSoft\ThreadFlow\Contracts\Page;

use Closure;

interface ActivePagesStorageFactoryInterface
{
    public function registerDriver(string $name, Closure $callback): void;

    public function make(string $name): ActivePagesStorageInterface;
}
