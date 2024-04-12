<?php

namespace SequentSoft\ThreadFlow\Contracts\PendingMessages;

use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\Regular\OutgoingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;

interface PendingMessagesRepositoryInterface
{
    public function pushTransitionToPage(
        MessageContextInterface $context,
        SessionInterface $session,
        PageInterface $page
    ): void;

    public function pushOutgoingMessage(
        MessageContextInterface $context,
        SessionInterface $session,
        OutgoingMessageInterface $message
    ): void;

    public function pull(MessageContextInterface $context, SessionInterface $session): ?PendingMessageInterface;

    public function isEmpty(MessageContextInterface $context, SessionInterface $session): bool;
}
