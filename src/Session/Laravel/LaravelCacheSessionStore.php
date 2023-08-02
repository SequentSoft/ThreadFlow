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

    public function load(
        MessageContextInterface $context
    ): SessionInterface {
        $key = $this->makeKeyString($this->channelName, $context);

        $lock = Cache::lock("{$key}-lock", $this->getSessionMaxLockSeconds());

        $lock->block($this->getSessionMaxLockWaitSeconds());

        $session = Cache::store($this->getCacheStoreName())->get($key)
            ?? new Session();

        $session->setSaveCallback(fn(SessionInterface $session) => $this->save(
            $context,
            $session,
            $lock
        ));

        $session->setClosedCallback(fn() => $lock?->release());

        return $session;
    }

    protected function makeKeyString(string $channelName, MessageContextInterface $context): string
    {
        return $channelName . ':' . $context->getRoom()->getId();
    }

    protected function getSessionMaxLockSeconds(): int
    {
        return $this->config->get('session_max_lock_seconds', 10);
    }

    protected function getSessionMaxLockWaitSeconds(): int
    {
        return $this->config->get('session_max_lock_wait_seconds', 15);
    }

    protected function getCacheStoreName(): string
    {
        return $this->config->get('session_store', 'file');
    }

    protected function getMaxBackgroundPageStates(): int
    {
        return $this->config->get('session_background_max', 5);
    }

    public function save(MessageContextInterface $context, SessionInterface $session, ?Lock $lock = null): void
    {
        $key = $this->makeKeyString($this->channelName, $context);

        $session->getBackgroundPageStates()
            ->truncate($this->getMaxBackgroundPageStates());

        Cache::store($this->getCacheStoreName())->put($key, $session);

        $lock?->release();
    }
}
