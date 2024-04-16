<?php

namespace SequentSoft\ThreadFlow\Laravel\PendingMessages\StorageDrivers;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Config\ConfigInterface;
use SequentSoft\ThreadFlow\Contracts\PendingMessages\PendingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\PendingMessages\PendingMessagesStorageInterface;
use SequentSoft\ThreadFlow\Contracts\Serializers\SerializerInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;

class CachePendingMessagesStorage implements PendingMessagesStorageInterface
{
    public function __construct(
        protected ConfigInterface $config,
        protected SerializerInterface $serializer,
    ) {
    }

    protected function getCacheStore(): Repository
    {
        return Cache::store(
            $this->config->get('store')
        );
    }

    protected function makeKeyString(MessageContextInterface $context, SessionInterface $session, string $n): string
    {
        $channelName = $context->getChannelName();
        $sessionId = $session->getId();

        return "{$channelName}:{$sessionId}:{$n}";
    }

    public function push(
        MessageContextInterface $context,
        SessionInterface $session,
        PendingMessageInterface $message
    ): void {
        $pendingMessagesCount = $session->get('$pendingMessagesCount', 0) + 1;
        $session->set('$pendingMessagesCount', $pendingMessagesCount);

        $this->getCacheStore()->put(
            $this->makeKeyString($context, $session, "pending-message-{$pendingMessagesCount}"),
            $this->serializer->serialize($message)
        );
    }

    public function pull(
        MessageContextInterface $context,
        SessionInterface $session
    ): ?PendingMessageInterface {
        $index = $session->get('$pendingMessagesIndex', 0) + 1;
        $session->set('$pendingMessagesIndex', $index);

        $serializedMessage = $this->getCacheStore()->pull(
            $this->makeKeyString($context, $session, "pending-message-{$index}")
        );

        return $this->serializer->unserialize($serializedMessage);
    }

    public function isEmpty(MessageContextInterface $context, SessionInterface $session): bool
    {
        $index = $session->get('$pendingMessagesIndex', 0);
        $count = $session->get('$pendingMessagesCount', 0);

        return $index >= $count;
    }
}
