<?php

namespace SequentSoft\ThreadFlow\Traits;

use Closure;

trait HasUserResolver
{
    protected ?Closure $userResolver = null;

    public function setUserResolver(?Closure $userResolver): void
    {
        $this->userResolver = $userResolver;
    }

    public function getUserResolver(): ?Closure
    {
        return $this->userResolver;
    }
}
