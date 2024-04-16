<?php

namespace SequentSoft\ThreadFlow\PendingMessages;

use Closure;
use InvalidArgumentException;
use SequentSoft\ThreadFlow\Config;
use SequentSoft\ThreadFlow\Contracts\Config\ConfigInterface;
use SequentSoft\ThreadFlow\Contracts\PendingMessages\PendingMessagesStorageFactoryInterface;
use SequentSoft\ThreadFlow\Contracts\PendingMessages\PendingMessagesStorageInterface;

class PendingMessagesStorageFactory implements PendingMessagesStorageFactoryInterface
{
    protected array $drivers = [];

    public function __construct(protected ConfigInterface $config)
    {
    }

    /**
     * Register a custom session store driver.
     */
    public function registerDriver(string $name, Closure $callback): void
    {
        $this->drivers[$name] = $callback;
    }

    /**
     * Make a new session store instance for the given channel.
     *
     * @throws InvalidArgumentException
     */
    public function make(string $name): PendingMessagesStorageInterface
    {
        $config = $this->config->get($name);

        $driverName = $config['driver'] ?? null;

        if ($driverName === null) {
            throw new InvalidArgumentException(
                "Pending messages storage {$name} is not configured."
            );
        }

        if (! isset($this->drivers[$driverName])) {
            throw new InvalidArgumentException(
                "Pending messages storage driver {$driverName} is not registered."
            );
        }

        return call_user_func(
            $this->drivers[$driverName],
            new Config($config)
        );
    }
}
