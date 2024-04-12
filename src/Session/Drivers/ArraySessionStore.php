<?php

namespace SequentSoft\ThreadFlow\Session\Drivers;

use Exception;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Config\ConfigInterface;
use SequentSoft\ThreadFlow\Contracts\Serializers\SerializerInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;

class ArraySessionStore extends BaseSessionStore
{
    public function __construct(
        protected string $channelName,
        protected ConfigInterface $config,
        protected SerializerInterface $serializer,
        protected ArraySessionStoreStorage $storage,
    ) {
        parent::__construct($channelName, $config, $serializer);
    }

    /**
     * @throws Exception
     */
    public function useSession(MessageContextInterface $context, callable $callback): mixed
    {
        $key = $this->makeKeyString($this->channelName, $context);

        $session = $this->load($key);

        $result = $this->run($session, $callback);

        $this->storage->store(
            $key,
            $this->serializer->serialize($session->toArray())
        );

        return $result;
    }

    protected function load(string $key): SessionInterface
    {
        $session = $this->serializer->unserialize(
            $this->storage->load($key)
        );

        return $this->makeFromData($session);
    }
}
