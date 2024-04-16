<?php

namespace SequentSoft\ThreadFlow\Laravel\Contracts\PendingMessages;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\PendingMessages\PendingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;

interface PendingMessageStoreModelInterface
{
    public function scopeFilterByContextAndSession(
        Builder $query,
        MessageContextInterface $context,
        SessionInterface $session
    ): Builder;

    public function getSerializedPendingMessage(): string;

    public function fillPendingMessage(string $serializedPendingMessage, PendingMessageInterface $page): PendingMessageStoreModelInterface&Model;

    public function fillSession(SessionInterface $session): PendingMessageStoreModelInterface&Model;

    public function fillContext(MessageContextInterface $context): PendingMessageStoreModelInterface&Model;
}
