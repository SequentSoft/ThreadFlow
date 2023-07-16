<?php

namespace SequentSoft\ThreadFlow\DataFetchers;

use Closure;
use SequentSoft\ThreadFlow\Contracts\DataFetchers\DataFetcherInterface;

class InvokableDataFetcher implements DataFetcherInterface
{
    protected Closure $handleUpdate;

    public function fetch(Closure $handleUpdate): void
    {
        $this->handleUpdate = $handleUpdate;
    }

    public function __invoke(array $update): void
    {
        call_user_func($this->handleUpdate, $update);
    }
}
