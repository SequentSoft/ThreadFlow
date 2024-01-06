<?php

namespace SequentSoft\ThreadFlow;

use SequentSoft\ThreadFlow\Contracts\Config\ConfigInterface;
use SequentSoft\ThreadFlow\Exceptions\Config\InvalidNestedConfigException;

class Config implements ConfigInterface
{
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->config[$key] ?? $default;
    }

    public function all(): array
    {
        return $this->config;
    }

    public function getNested(string $key): ConfigInterface
    {
        $value = $this->get($key);

        if (! is_array($value)) {
            throw new InvalidNestedConfigException('Invalid nested config: ' . $key);
        }

        return new Config($value);
    }
}
