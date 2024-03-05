<?php

namespace SequentSoft\ThreadFlow\Session;

use Closure;
use Exception;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionStoreInterface;

class ArraySessionStore implements SessionStoreInterface
{
    public function __construct(
        protected string $channelName,
        protected ArraySessionStoreStorage $storage,
    ) {
    }

    /**
     * @throws Exception
     */
    public function useSession(MessageContextInterface $context, Closure $callback): mixed
    {
        $key = $this->makeKeyString($this->channelName, $context);

        $session = $this->storage->load($key);

        $session = is_null($session)
            ? new Session()
            : $session;

        $result = $callback($session);

        $this->storage->store($key, $session);

        return $result;
    }

    protected function makeKeyString(string $channelName, MessageContextInterface $context): string
    {
        return $channelName . ':' . $context->getRoom()->getId();
    }
}
