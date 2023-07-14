<?php

namespace SequentSoft\ThreadFlow\Contracts\DataFetchers;

use Closure;

interface DataFetcherInterface
{
    public function fetch(Closure $handleUpdate): void;
}
