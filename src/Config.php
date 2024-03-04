<?php

namespace SequentSoft\ThreadFlow;

use SequentSoft\ThreadFlow\Contracts\Config\ConfigInterface;
use SequentSoft\ThreadFlow\Exceptions\Config\InvalidNestedConfigException;

class Config implements ConfigInterface
{
    public function __construct(
        protected array $config
    ) {
    }

    /**
     * Add or update a config value.
     * If the key already exists, it will be overwritten.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->config[$key] ?? $default;
    }

    /**
     * Get all config values.
     */
    public function all(): array
    {
        return $this->config;
    }

    /**
     * Get a nested config value.
     * If the key does not exist, an exception will be thrown.
     *
     * @throws InvalidNestedConfigException
     */
    public function getNested(string $key): ConfigInterface
    {
        $value = $this->get($key);

        if (! is_array($value)) {
            throw new InvalidNestedConfigException($this, $key);
        }

        return new Config($value);
    }
}
