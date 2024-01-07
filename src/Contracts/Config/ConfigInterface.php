<?php

namespace SequentSoft\ThreadFlow\Contracts\Config;

interface ConfigInterface
{
    public function get(string $key, mixed $default = null): mixed;

    public function all(): array;

    public function getNested(string $key): ConfigInterface;
}
