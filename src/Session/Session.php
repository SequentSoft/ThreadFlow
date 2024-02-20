<?php

namespace SequentSoft\ThreadFlow\Session;

use Exception;
use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionDataInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;
use SequentSoft\ThreadFlow\Traits\HasUserResolver;

class Session implements SessionInterface
{
    use HasUserResolver;

    protected SessionDataInterface $data;

    protected SessionDataInterface $serviceData;

    protected ?PageInterface $currentPage;

    protected array $pendingInteractions = [];

    /**
     * @throws Exception
     */
    final public function __construct(
        SessionDataInterface|array $data = [],
        ?PageInterface $currentPage = null,
        SessionDataInterface|array $serviceData = [],
        array $pendingInteractions = [],
    ) {
        $this->data = $data instanceof SessionDataInterface
            ? $data
            : SessionData::create($data);

        $this->currentPage = $currentPage;

        $this->serviceData = $serviceData instanceof SessionDataInterface
            ? $serviceData
            : SessionData::create($serviceData);

        $this->pendingInteractions = $pendingInteractions;
    }

    public function getUser(): mixed
    {
        return $this->userResolver
            ? call_user_func($this->userResolver, $this)
            : null;
    }

    /**
     * @throws Exception
     */
    public function reset(): void
    {
        $this->data = SessionData::create();
        $this->serviceData = SessionData::create();
        $this->currentPage = null;
        $this->pendingInteractions = [];
    }

    public function pushPendingInteraction(mixed $interaction): void
    {
        $this->pendingInteractions[] = $interaction;
    }

    public function takePendingInteraction(): mixed
    {
        return array_shift($this->pendingInteractions);
    }

    public function hasPendingInteractions(): bool
    {
        return ! empty($this->pendingInteractions);
    }

    public function getData(): SessionDataInterface
    {
        return $this->data;
    }

    public function getServiceData(): SessionDataInterface
    {
        return $this->serviceData;
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
            'data' => $this->data->all(),
            'currentPage' => $this->currentPage,
            'serviceData' => $this->serviceData->all(),
            'pendingInteractions' => $this->pendingInteractions,
        ];
    }

    public static function fromArray(array $data): SessionInterface
    {
        return new static(
            SessionData::create($data['data']),
            $data['currentPage'],
            SessionData::create($data['serviceData']),
            $data['pendingInteractions'],
        );
    }

    public function __serialize(): array
    {
        return $this->toArray();
    }

    public function __unserialize(array $data): void
    {
        $this->data = SessionData::create($data['data']);
        $this->currentPage = $data['currentPage'];
        $this->serviceData = SessionData::create($data['serviceData']);
        $this->pendingInteractions = $data['pendingInteractions'];
    }
}
