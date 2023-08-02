<?php

namespace SequentSoft\ThreadFlow\Contracts\Config;

interface ConfigInterface extends SimpleConfigInterface
{
    public function all(): array;

    public function getNested(string $key): ConfigInterface;
}
