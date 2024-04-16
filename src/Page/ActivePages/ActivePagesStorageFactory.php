<?php

namespace SequentSoft\ThreadFlow\Page\ActivePages;

use Closure;
use InvalidArgumentException;
use SequentSoft\ThreadFlow\Config;
use SequentSoft\ThreadFlow\Contracts\Config\ConfigInterface;
use SequentSoft\ThreadFlow\Contracts\Page\ActivePagesStorageFactoryInterface;
use SequentSoft\ThreadFlow\Contracts\Page\ActivePagesStorageInterface;

class ActivePagesStorageFactory implements ActivePagesStorageFactoryInterface
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
    public function make(string $name): ActivePagesStorageInterface
    {
        $config = $this->config->get($name);

        $driverName = $config['driver'] ?? null;

        if ($driverName === null) {
            throw new InvalidArgumentException(
                "Active pages storage {$name} is not configured."
            );
        }

        if (! isset($this->drivers[$driverName])) {
            throw new InvalidArgumentException(
                "Active pages storage driver {$driverName} is not registered."
            );
        }

        return call_user_func(
            $this->drivers[$driverName],
            new Config($config)
        );
    }
}
