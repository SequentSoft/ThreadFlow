<?php

namespace SequentSoft\ThreadFlow\Session\Laravel;

use Closure;
use Illuminate\Support\Facades\Cache;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Config\ConfigInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionStoreInterface;
use SequentSoft\ThreadFlow\Exceptions\Session\SessionSizeLimitExceededException;
use SequentSoft\ThreadFlow\Session\Session;

class LaravelCacheSessionStore implements SessionStoreInterface
{
    public function __construct(
        protected string $channelName,
        protected ConfigInterface $config,
    ) {
    }

    public function useSession(MessageContextInterface $context, Closure $callback): mixed
    {
        $key = $this->makeKeyString($this->channelName, $context);

        $lock = Cache::lock("{$key}-lock", $this->getSessionMaxLockSeconds());

        $lock->block($this->getSessionMaxLockWaitSeconds());

        $session = Cache::store($this->getCacheStoreName())->get($key)
            ?? new Session();

        if (! $session instanceof SessionInterface) {
            $session = $this->fixBrokenSession($session);
        }

        $result = $callback($session);

        $session->getBackgroundPageStates()
            ->truncate($this->getMaxBackgroundPageStates());

        $sessionSize = $this->calculateSize($session);

        if ($sessionSize > $this->getMaxSize()) {
            throw new SessionSizeLimitExceededException(
                session: $session,
                size: $sessionSize,
                limit: $this->getMaxSize()
            );
        }

        Cache::store($this->getCacheStoreName())->put($key, $session);

        $lock?->release();

        return $result;
    }

    protected function fixBrokenSession(mixed $brokenSession): SessionInterface
    {
        return new Session();
    }

    protected function makeKeyString(string $channelName, MessageContextInterface $context): string
    {
        return $channelName.':'.$context->getRoom()->getId();
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
        return $this->config->get('session_store_name', 'file');
    }

    protected function getMaxBackgroundPageStates(): int
    {
        return $this->config->get('session_background_max', 5);
    }

    protected function getMaxSize(): int
    {
        return $this->config->get('session_max_size', 1024 * 1024 * 0.5); // 512 KB by default
    }

    protected function calculateSize(SessionInterface $session): int
    {
        return strlen(serialize($session));
    }
}
