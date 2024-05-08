<?php

namespace SequentSoft\ThreadFlow\Contracts\Session;

use Closure;

interface SessionStoreFactoryInterface
{
    public function registerDriver(string $name, Closure $callback): void;

    public function make(string $name): SessionStoreInterface;
}
