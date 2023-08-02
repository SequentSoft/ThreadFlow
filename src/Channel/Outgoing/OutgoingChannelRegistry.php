<?php

namespace SequentSoft\ThreadFlow\Channel\Outgoing;

use Closure;
use SequentSoft\ThreadFlow\Contracts\Channel\Outgoing\OutgoingChannelInterface;
use SequentSoft\ThreadFlow\Contracts\Channel\Outgoing\OutgoingChannelRegistryInterface;
use SequentSoft\ThreadFlow\Contracts\Config\ConfigInterface;
use SequentSoft\ThreadFlow\Exceptions\Channel\ChannelNotConfiguredException;
use SequentSoft\ThreadFlow\Exceptions\Channel\ChannelNotFoundException;
use SequentSoft\ThreadFlow\Exceptions\Config\InvalidNestedConfigException;

class OutgoingChannelRegistry implements OutgoingChannelRegistryInterface
{
    /**
     * @var Closure[]
     */
    protected array $channelDrivers = [];

    public function register(string $driverName, Closure $callback): void
    {
        $this->channelDrivers[$driverName] = $callback;
    }

    public function get(string $name, ConfigInterface $config): OutgoingChannelInterface
    {
        $driverName = $config->get('driver');

        if ($driverName === null) {
            throw new ChannelNotConfiguredException(
                "Incoming channel [{$name}] is not configured. Driver name is missing."
            );
        }

        if (! isset($this->channelDrivers[$driverName])) {
            throw new ChannelNotFoundException("Outgoing channel driver [{$driverName}] is not registered.");
        }

        return call_user_func($this->channelDrivers[$driverName], $config);
    }
}
