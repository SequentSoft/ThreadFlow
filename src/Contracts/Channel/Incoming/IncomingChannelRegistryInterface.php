<?php

namespace SequentSoft\ThreadFlow\Contracts\Channel\Incoming;

use Closure;
use SequentSoft\ThreadFlow\Contracts\Config\ConfigInterface;

interface IncomingChannelRegistryInterface
{
    public function register(string $driverName, Closure $callback): void;

    public function get(string $name, ConfigInterface $config): IncomingChannelInterface;
}
