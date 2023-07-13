<?php

namespace SequentSoft\ThreadFlow\Contracts\Channel\Outgoing;

use Closure;
use SequentSoft\ThreadFlow\Contracts\Config\ConfigInterface;

interface OutgoingChannelRegistryInterface
{
    public function register(string $driverName, Closure $callback): void;

    public function get(string $name, ConfigInterface $config): OutgoingChannelInterface;
}
