<?php

namespace SequentSoft\ThreadFlow\Session;

use InvalidArgumentException;
use Closure;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Config\ConfigInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionStoreInterface;

class ArraySessionStore implements SessionStoreInterface
{
    public function __construct(
        protected string $channelName,
        protected ConfigInterface $config,
        protected ArraySessionStoreStorage $storage,
    ) {
    }

    protected function makeKeyString(string $channelName, MessageContextInterface $context): string
    {
        return $channelName . ':' . $context->getRoom()->getId();
    }

    public function load(
        MessageContextInterface $context
    ): SessionInterface {
        $key = $this->makeKeyString($this->channelName, $context);

        $data = $this->storage->load($key);

        return new Session(
            $data ?? [],
            fn(SessionInterface $session) => $this->save(
                $context,
                $session
            )
        );
    }

    public function save(MessageContextInterface $context, SessionInterface $session): void
    {
        $key = $this->makeKeyString($this->channelName, $context);

        $this->storage->store($key, $session->getData());
    }
}
