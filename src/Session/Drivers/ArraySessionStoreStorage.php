<?php

namespace SequentSoft\ThreadFlow\Session\Drivers;

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
