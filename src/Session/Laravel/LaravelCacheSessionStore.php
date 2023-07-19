<?php

namespace SequentSoft\ThreadFlow\Session\Laravel;

use Illuminate\Support\Facades\Cache;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Config\ConfigInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionStoreInterface;
use SequentSoft\ThreadFlow\Session\Session;
use Illuminate\Contracts\Cache\Lock;

class LaravelCacheSessionStore implements SessionStoreInterface
{
    public function __construct(
        protected string $channelName,
        protected ConfigInterface $config,
    ) {
    }

    protected function makeKeyString(string $channelName, MessageContextInterface $context): string
    {
        return $channelName . ':' . $context->getRoom()->getId();
    }

    protected function getCacheStoreName(): string
    {
        return $this->config->get('session_store', 'file');
    }

    protected function getSessionMaxLockSeconds(): int
    {
        return $this->config->get('session_max_lock_seconds', 10);
    }

    protected function getSessionMaxLockWaitSeconds(): int
    {
        return $this->config->get('session_max_lock_wait_seconds', 15);
    }

    public function load(
        MessageContextInterface $context
    ): SessionInterface {
        $key = $this->makeKeyString($this->channelName, $context);

        $lock = Cache::lock("{$key}-lock", $this->getSessionMaxLockSeconds());

        $lock->block($this->getSessionMaxLockWaitSeconds());

        $data = Cache::store($this->getCacheStoreName())->get($key);

        return new Session(
            $data ?? [],
            fn(SessionInterface $session) => $this->save(
                $context,
                $session,
                $lock
            ),
            fn () => $lock?->release(),
        );
    }

    public function save(MessageContextInterface $context, SessionInterface $session, ?Lock $lock = null): void
    {
        $key = $this->makeKeyString($this->channelName, $context);

        Cache::store($this->getCacheStoreName())->put($key, $session->getData());

        $lock?->release();
    }
}
