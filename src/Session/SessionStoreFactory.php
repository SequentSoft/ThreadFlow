<?php

namespace SequentSoft\ThreadFlow\Session;

use Closure;
use InvalidArgumentException;
use SequentSoft\ThreadFlow\Contracts\Config\ConfigInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionStoreFactoryInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionStoreInterface;

class SessionStoreFactory implements SessionStoreFactoryInterface
{
    protected array $registeredSessionStores = [];

    public function register(string $name, Closure $callback): void
    {
        $this->registeredSessionStores[$name] = $callback;
    }

    public function make(string $name, string $channelName, ConfigInterface $config): SessionStoreInterface
    {
        if (!isset($this->registeredSessionStores[$name])) {
            throw new InvalidArgumentException("Session store {$name} is not registered.");
        }

        return call_user_func($this->registeredSessionStores[$name], $channelName, $config);
    }
}
