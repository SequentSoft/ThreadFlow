<?php

namespace SequentSoft\ThreadFlow\Contracts\Session;

interface SessionDataInterface
{
    public static function create(array $data = []): SessionDataInterface;

    public function all(): array;

    public function delete(string $key): void;

    public function get(string $key, mixed $default = null): mixed;

    public function set(string $key, mixed $data): void;
}
