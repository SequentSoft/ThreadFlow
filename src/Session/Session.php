<?php

namespace SequentSoft\ThreadFlow\Session;

use Exception;
use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionDataInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;
use SequentSoft\ThreadFlow\Traits\GenerateUniqueIdsTrait;

class Session implements SessionInterface
{
    use GenerateUniqueIdsTrait;

    protected string $id;

    protected SessionDataInterface $data;

    protected ?PageInterface $currentPage;

    /**
     * @throws Exception
     */
    final public function __construct(
        SessionDataInterface|array $data = [],
        ?PageInterface $currentPage = null,
        ?string $id = null,
    ) {
        $this->id = $id ?? static::generateUniqueId();

        $this->data = $data instanceof SessionDataInterface
            ? $data
            : SessionData::create($data);

        $this->currentPage = $currentPage;
    }

    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @throws Exception
     */
    public function reset(): void
    {
        $this->data = SessionData::create();
        $this->currentPage = null;
        $this->id = static::generateUniqueId();
    }

    public function getData(): SessionDataInterface
    {
        return $this->data;
    }

    public function getCurrentPage(): ?PageInterface
    {
        return $this->currentPage;
    }

    public function setCurrentPage(?PageInterface $currentPage): void
    {
        $this->currentPage = $currentPage;
    }

    public function delete(string $key): void
    {
        $this->data->delete($key);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data->get($key, $default);
    }

    public function set(string $key, mixed $data): void
    {
        $this->data->set($key, $data);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'data' => $this->data->all(),
            'currentPage' => $this->currentPage,
        ];
    }

    public static function fromArray(array $data): SessionInterface
    {
        return new static(
            SessionData::create($data['data']),
            $data['currentPage'],
            $data['id'] ?? static::generateUniqueId(),
        );
    }

    public function __serialize(): array
    {
        return $this->toArray();
    }

    public function __unserialize(array $data): void
    {
        $this->id = $data['id'] ?? static::generateUniqueId();
        $this->data = SessionData::create($data['data']);
        $this->currentPage = $data['currentPage'];
    }
}
