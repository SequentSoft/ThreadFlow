<?php

namespace SequentSoft\ThreadFlow\Contracts\Config;

interface SimpleConfigInterface
{
    public function get(string $key, $default = null);
}
