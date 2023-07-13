<?php

namespace SequentSoft\ThreadFlow\Contracts\Config;

interface ConfigInterface extends SimpleConfigInterface
{
    public function isEmpty(): bool;

    public function all(): array;

    public function merge(ConfigInterface $config): self;

    public function getNested(string $key): ConfigInterface;
}
