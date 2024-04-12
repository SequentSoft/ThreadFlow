<?php

namespace SequentSoft\ThreadFlow\Contracts\PendingMessages;

use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;

interface PendingMessagesStorageInterface
{
    public function push(
        MessageContextInterface $context,
        SessionInterface $session,
        PendingMessageInterface $message
    ): void;

    public function pull(
        MessageContextInterface $context,
        SessionInterface $session
    ): ?PendingMessageInterface;

    public function isEmpty(MessageContextInterface $context, SessionInterface $session): bool;
}
