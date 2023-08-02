<?php

namespace SequentSoft\ThreadFlow\Contracts\Config;

interface SimpleConfigInterface
{
    public function get(string $key, mixed $default = null): mixed;
}
