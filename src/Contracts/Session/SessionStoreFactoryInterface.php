<?php

namespace SequentSoft\ThreadFlow\Contracts\Session;

use Closure;
use SequentSoft\ThreadFlow\Contracts\Config\ConfigInterface;

interface SessionStoreFactoryInterface
{
    public function register(string $name, Closure $callback): void;

    public function make(string $name, string $channelName, ConfigInterface $config): SessionStoreInterface;
}
