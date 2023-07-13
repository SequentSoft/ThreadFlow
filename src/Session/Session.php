<?php

namespace SequentSoft\ThreadFlow\Session;

use Closure;
use RuntimeException;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;

class Session implements SessionInterface
{
    protected bool $isClosed = false;

    public function __construct(
        protected array $data,
        protected Closure $saveCallback,
        protected ?Closure $discardCallback = null
    ) {}

    public function __destruct()
    {
        if ($this->discardCallback) {
            call_user_func($this->discardCallback, $this);
        }
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function delete(string $key): void
    {
        if ($this->isClosed) {
            throw new RuntimeException('Session is closed.');
        }

        unset($this->data[$key]);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    public function set(string $key, mixed $data): void
    {
        if ($this->isClosed) {
            throw new RuntimeException('Session is closed.');
        }

        $this->data[$key] = $data;
    }

    public function nested(string $key): SessionInterface
    {
        return new static(
            $this->get($key, []),
            function (SessionInterface $session) use ($key) {
                $this->set($key, $session->getData());
            }
        );
    }

    public function close(): void
    {
        if ($this->isClosed) {
            return;
        }

        call_user_func($this->saveCallback, $this);
        $this->isClosed = true;
    }
}
