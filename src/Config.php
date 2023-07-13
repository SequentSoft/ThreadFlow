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

    public function isEmpty(): bool
    {
        return empty($this->config);
    }

    /**
     * Get a config value by its key.
     *
     * @param string $key The config key.
     * @param mixed $default The default value if the config key does not exist.
     * @return mixed The config value.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->config[$key] ?? $default;
    }

    public function all(): array
    {
        return $this->config;
    }

    public function merge(ConfigInterface $config): self
    {
        $this->config = array_merge($this->config, $config->all());

        return $this;
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
