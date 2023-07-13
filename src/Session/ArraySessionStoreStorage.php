<?php

namespace SequentSoft\ThreadFlow\Session;

use InvalidArgumentException;
use Closure;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Config\ConfigInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionStoreInterface;

class ArraySessionStoreStorage
{
    protected array $storage = [];

    public function store(string $key, mixed $data): void
    {
        $this->storage[$key] = $data;
    }

    public function load(string $key): mixed
    {
        return $this->storage[$key] ?? null;
    }
}
