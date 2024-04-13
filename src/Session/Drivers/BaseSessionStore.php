<?php

namespace SequentSoft\ThreadFlow\Session\Drivers;

use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Config\ConfigInterface;
use SequentSoft\ThreadFlow\Contracts\Serializers\SerializerInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;
use SequentSoft\ThreadFlow\Exceptions\Session\SessionSizeLimitExceededException;
use SequentSoft\ThreadFlow\Session\Session;
use SequentSoft\ThreadFlow\Contracts\Session\SessionStoreInterface;

abstract class BaseSessionStore implements SessionStoreInterface
{
    public function __construct(
        protected string $channelName,
        protected ConfigInterface $config,
        protected SerializerInterface $serializer,
    ) {
    }

    protected function makeFromData(mixed $data): SessionInterface
    {
        return is_array($data)
            ? Session::fromArray($data)
            : new Session();
    }

    protected function run(SessionInterface $session, callable $callback): mixed
    {
        $result = $callback($session);

        $sessionSize = $this->calculateSize($session);

        if ($sessionSize > $this->getMaxSize()) {
            throw new SessionSizeLimitExceededException(
                session: $session,
                size: $sessionSize,
                limit: $this->getMaxSize()
            );
        }

        return $result;
    }

    protected function calculateSize(SessionInterface $session): int
    {
        return strlen(serialize($session));
    }

    protected function getMaxSize(): int
    {
        return $this->getConfig()->get('max_size', 1024 * 1024 * 0.5); // 512 KB by default
    }

    protected function makeKeyString(string $channelName, MessageContextInterface $context): string
    {
        $roomId = $context->getRoom()->getId();
        $participantId = $context->getParticipant()->getId();

        return "{$channelName}:{$roomId}:{$participantId}";
    }

    protected function getConfig(): ConfigInterface
    {
        return $this->config;
    }
}