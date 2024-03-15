<?php

namespace SequentSoft\ThreadFlow\Laravel\Session;

use Illuminate\Contracts\Cache\Lock;
use Illuminate\Support\Facades\Cache;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Config\ConfigInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionStoreInterface;
use Throwable;

class CacheSessionStore extends BaseSessionStore implements SessionStoreInterface
{
    public function __construct(
        protected string $channelName,
        protected ConfigInterface $config,
    ) {
    }

    protected function load(string $key): SessionInterface
    {
        return $this->makeFromData(
            Cache::store($this->getCacheStoreName())->get($key)
        );
    }

    protected function store(string $key, SessionInterface $session): void
    {
        Cache::store($this->getCacheStoreName())->put($key, $session->toArray());
    }

    protected function acquireLock(string $key): Lock
    {
        $lock = Cache::lock("{$key}-lock", $this->getSessionMaxLockSeconds());

        $lock->block($this->getSessionMaxLockWaitSeconds());

        return $lock;
    }

    public function useSession(MessageContextInterface $context, callable $callback): mixed
    {
        $key = $this->makeKeyString($this->channelName, $context);
        $lock = $this->acquireLock($key);
        $session = $this->load($key);

        try {
            $result = $this->run($session, $callback);
        } catch (Throwable $e) {
            $lock->release();

            throw $e;
        }

        $this->store($key, $session);
        $lock->release();

        return $result;
    }

    protected function makeKeyString(string $channelName, MessageContextInterface $context): string
    {
        $id = $context->getRoom()->getId();

        return "{$channelName}:{$id}";
    }

    protected function getSessionMaxLockSeconds(): int
    {
        return $this->config->get('max_lock_seconds', 10);
    }

    protected function getSessionMaxLockWaitSeconds(): int
    {
        return $this->config->get('max_lock_wait_seconds', 15);
    }

    protected function getCacheStoreName(): string
    {
        return $this->config->get('store', 'file');
    }

    protected function getMaxBackgroundPageStates(): int
    {
        return $this->config->get('background_max', 5);
    }

    protected function getConfig(): ConfigInterface
    {
        return $this->config;
    }
}
