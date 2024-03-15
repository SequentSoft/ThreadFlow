<?php

namespace SequentSoft\ThreadFlow\Session;

use Exception;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;
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
    public function useSession(MessageContextInterface $context, callable $callback): mixed
    {
        $key = $this->makeKeyString($this->channelName, $context);

        $session = $this->load($key);

        $result = $callback($session);

        $this->storage->store($key, $session);

        return $result;
    }

    protected function load(string $key): SessionInterface
    {
        $session = $this->storage->load($key);

        return is_null($session)
            ? new Session()
            : $session;
    }

    protected function makeKeyString(string $channelName, MessageContextInterface $context): string
    {
        $id = $context->getRoom()->getId();

        return "{$channelName}:{$id}";
    }
}
