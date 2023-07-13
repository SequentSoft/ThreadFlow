<?php

namespace SequentSoft\ThreadFlow\Contracts\Session;

interface SessionInterface
{
    public function getData(): array;

    public function delete(string $key): void;

    public function get(string $key, mixed $default = null): mixed;

    public function set(string $key, mixed $data): void;

    public function nested(string $key): SessionInterface;

    public function close(): void;
}
