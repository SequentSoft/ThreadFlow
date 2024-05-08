<?php

namespace SequentSoft\ThreadFlow\Session;

use Closure;
use InvalidArgumentException;
use SequentSoft\ThreadFlow\Config;
use SequentSoft\ThreadFlow\Contracts\Config\ConfigInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionStoreFactoryInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionStoreInterface;

class SessionStoreFactory implements SessionStoreFactoryInterface
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
    public function make(string $name): SessionStoreInterface
    {
        $config = $this->config->get($name);

        $driverName = $config['driver'] ?? null;

        if ($driverName === null) {
            throw new InvalidArgumentException(
                "Session store {$name} is not configured."
            );
        }

        if (! isset($this->drivers[$driverName])) {
            throw new InvalidArgumentException(
                "Session store driver {$driverName} is not registered."
            );
        }

        return call_user_func($this->drivers[$driverName], new Config($config));
    }
}
