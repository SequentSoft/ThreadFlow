<?php

namespace SequentSoft\ThreadFlow\Session;

use SequentSoft\ThreadFlow\Contracts\Session\SessionDataInterface;

class SessionData implements SessionDataInterface
{
    final public function __construct(
        protected array $data = []
    ) {
    }

    public static function create(array $data = []): SessionDataInterface
    {
        return new static($data);
    }

    public function all(): array
    {
        return $this->data;
    }

    public function delete(string $key): void
    {
        unset($this->data[$key]);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    public function set(string $key, mixed $data): void
    {
        $this->data[$key] = $data;
    }

    public function __serialize(): array
    {
        return $this->data;
    }

    public function __unserialize(array $data): void
    {
        $this->data = $data;
    }
}
