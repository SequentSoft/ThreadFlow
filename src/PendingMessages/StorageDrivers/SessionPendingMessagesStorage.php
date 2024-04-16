<?php

namespace SequentSoft\ThreadFlow\PendingMessages\StorageDrivers;

use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Config\ConfigInterface;
use SequentSoft\ThreadFlow\Contracts\PendingMessages\PendingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\PendingMessages\PendingMessagesStorageInterface;
use SequentSoft\ThreadFlow\Contracts\Serializers\SerializerInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;

class SessionPendingMessagesStorage implements PendingMessagesStorageInterface
{
    public function __construct(
        protected ConfigInterface $config,
        protected SerializerInterface $serializer,
    ) {
    }

    public function push(
        MessageContextInterface $context,
        SessionInterface $session,
        PendingMessageInterface $message
    ): void {
        $messages = $session->getData()->get('$pendingMessages', []);
        $messages[] = $message;
        $session->getData()->set('$pendingMessages', $messages);
    }

    public function pull(
        MessageContextInterface $context,
        SessionInterface $session
    ): ?PendingMessageInterface {
        $messages = $session->getData()->get('$pendingMessages', []);

        if (empty($messages)) {
            return null;
        }

        $message = array_shift($messages);

        $session->getData()->set('$pendingMessages', $messages);

        return $message;
    }

    public function isEmpty(MessageContextInterface $context, SessionInterface $session): bool
    {
        return empty($session->getData()->get('$pendingMessages', []));
    }
}
