<?php

namespace SequentSoft\ThreadFlow\PendingMessages;

use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\Regular\OutgoingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;
use SequentSoft\ThreadFlow\Contracts\PendingMessages\PendingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\PendingMessages\PendingMessagesRepositoryInterface;
use SequentSoft\ThreadFlow\Contracts\PendingMessages\PendingMessagesStorageInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;

class PendingMessagesRepository implements PendingMessagesRepositoryInterface
{
    public function __construct(
        protected PendingMessagesStorageInterface $pendingMessagesStorage
    ) {
    }

    public function pushTransitionToPage(
        MessageContextInterface $context,
        SessionInterface $session,
        PageInterface $page
    ): void {
        $this->pendingMessagesStorage->push($context, $session, new PendingMessage($page));
    }

    public function pushOutgoingMessage(
        MessageContextInterface $context,
        SessionInterface $session,
        OutgoingMessageInterface $message
    ): void {
        $this->pendingMessagesStorage->push($context, $session, new PendingMessage($message));
    }

    public function pull(MessageContextInterface $context, SessionInterface $session): ?PendingMessageInterface
    {
        return $this->pendingMessagesStorage->pull($context, $session);
    }

    public function isEmpty(MessageContextInterface $context, SessionInterface $session): bool
    {
        return $this->pendingMessagesStorage->isEmpty($context, $session);
    }
}
