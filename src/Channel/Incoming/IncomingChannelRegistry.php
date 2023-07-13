<?php

namespace SequentSoft\ThreadFlow\Channel\Incoming;

use Closure;
use SequentSoft\ThreadFlow\Contracts\Channel\Incoming\IncomingChannelInterface;
use SequentSoft\ThreadFlow\Contracts\Channel\Incoming\IncomingChannelRegistryInterface;
use SequentSoft\ThreadFlow\Contracts\Config\ConfigInterface;
use SequentSoft\ThreadFlow\Exceptions\Channel\ChannelNotConfiguredException;
use SequentSoft\ThreadFlow\Exceptions\Channel\ChannelNotFoundException;
use SequentSoft\ThreadFlow\Exceptions\Config\InvalidNestedConfigException;

class IncomingChannelRegistry implements IncomingChannelRegistryInterface
{
    /**
     * @var Closure[]
     */
    protected array $channelDrivers = [];

    public function register(string $driverName, Closure $callback): void
    {
        $this->channelDrivers[$driverName] = $callback;
    }

    public function get(string $name, ConfigInterface $config): IncomingChannelInterface
    {
        $driverName = $config->get('driver');

        if (!isset($this->channelDrivers[$driverName])) {
            throw new ChannelNotFoundException("Incoming channel {$driverName} is not registered.");
        }

        return call_user_func($this->channelDrivers[$driverName], $config);
    }
}
