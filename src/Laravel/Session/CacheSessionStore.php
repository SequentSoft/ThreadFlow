<?php

namespace SequentSoft\ThreadFlow\Laravel\Session;

use Illuminate\Contracts\Cache\Lock;
use Illuminate\Contracts\Cache\LockProvider;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;
use SequentSoft\ThreadFlow\Session\Drivers\BaseSessionStore;
use Throwable;

class CacheSessionStore extends BaseSessionStore
{
    protected function getCacheStore(): Repository&LockProvider
    {
        $cacheStore = Cache::store($this->config->get('store', 'file'));

        if (! $cacheStore instanceof LockProvider) {
            throw new \RuntimeException('Cache store must implement LockProvider interface');
        }

        return $cacheStore;
    }

    protected function load(string $key): SessionInterface
    {
        return $this->makeFromData(
            $this->getCacheStore()->get($key)
        );
    }

    protected function store(string $key, SessionInterface $session): void
    {
        $this->getCacheStore()->put($key, $session->toArray());
    }

    protected function acquireLock(string $key): Lock
    {
        $lock = $this->getCacheStore()->lock(
            "{$key}-lock",
            $this->config->get('max_lock_seconds', 10)
        );

        $lock->block(
            $this->config->get('max_lock_wait_seconds', 15)
        );

        return $lock;
    }

    public function useSession(MessageContextInterface $context, callable $callback): mixed
    {
        $key = $context->asKey();
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
}
