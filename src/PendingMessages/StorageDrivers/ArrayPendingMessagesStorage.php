<?php

namespace SequentSoft\ThreadFlow\PendingMessages\StorageDrivers;

use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Config\ConfigInterface;
use SequentSoft\ThreadFlow\Contracts\PendingMessages\PendingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\PendingMessages\PendingMessagesStorageInterface;
use SequentSoft\ThreadFlow\Contracts\Serializers\SerializerInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;

class ArrayPendingMessagesStorage implements PendingMessagesStorageInterface
{
    protected array $pendingMessages = [];

    public function __construct(
        protected ConfigInterface $config,
        protected SerializerInterface $serializer,
    ) {
    }

    protected function makeKey(MessageContextInterface $context, SessionInterface $session): string
    {
        return implode(':', [
            $context->getChannelName(),
            $context->getRoom()->getId(),
            $context->getParticipant()->getId(),
            $session->getId(),
        ]);
    }

    public function push(
        MessageContextInterface $context,
        SessionInterface $session,
        PendingMessageInterface $message
    ): void {
        $this->pendingMessages[$this->makeKey($context, $session)][] = $message;
    }

    public function pull(
        MessageContextInterface $context,
        SessionInterface $session
    ): ?PendingMessageInterface {
        if (! isset($this->pendingMessages[$this->makeKey($context, $session)])) {
            return null;
        }

        return array_shift($this->pendingMessages[$this->makeKey($context, $session)]);
    }

    public function isEmpty(MessageContextInterface $context, SessionInterface $session): bool
    {
        $key = $this->makeKey($context, $session);

        return (! isset($this->pendingMessages[$key])) || empty($this->pendingMessages[$key]);
    }
}
