<?php

namespace SequentSoft\ThreadFlow\Traits;

trait GenerateUniqueIdsTrait
{
    public static function generateUniqueId(): string
    {
        return base64_encode(random_bytes(24));
    }
}
