<?php

namespace SequentSoft\ThreadFlow\Exceptions\Config;

use Exception;
use SequentSoft\ThreadFlow\Contracts\Config\ConfigInterface;

class InvalidNestedConfigException extends Exception
{
    public function __construct(protected ConfigInterface $config, string $key)
    {
        parent::__construct(
            "The nested config key [{$key}] does not exist in the config."
        );
    }

    public function getConfig(): ConfigInterface
    {
        return $this->config;
    }
}
