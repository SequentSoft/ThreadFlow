<?php

namespace SequentSoft\ThreadFlow\Laravel\Models;

use Illuminate\Contracts\Database\Eloquent\Builder as BuilderInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\PendingMessages\PendingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;
use SequentSoft\ThreadFlow\Laravel\Contracts\PendingMessages\PendingMessageStoreModelInterface;

class ThreadFlowPendingMessage extends Model implements PendingMessageStoreModelInterface
{
    public const UPDATED_AT = null;

    public $fillable = [
        'session_id',
        'channel_name',
        'channel_context',
        'data',
    ];

    public $casts = [
        'session_id' => 'string',
        'channel_name' => 'string',
        'channel_context' => 'string',
        'data' => 'string',
    ];

    protected function makeChannelContextKey(MessageContextInterface $context): string
    {
        return implode(':', [
            $context->getRoom()->getId(),
            $context->getParticipant()->getId(),
        ]);
    }

    public function scopeFilterByContextAndSession(
        Builder|BuilderInterface $query,
        MessageContextInterface $context,
        SessionInterface $session
    ): Builder|BuilderInterface {
        return $query->where('channel_name', $context->getChannelName())
            ->where('channel_context', $this->makeChannelContextKey($context))
            ->where('session_id', $session->getId());
    }

    public function getSerializedPendingMessage(): string
    {
        return $this->attributes['data'];
    }

    public function fillPendingMessage(string $serializedPendingMessage, PendingMessageInterface $page): PendingMessageStoreModelInterface&Model
    {
        $this->attributes['data'] = $serializedPendingMessage;

        return $this;
    }

    public function fillSession(SessionInterface $session): PendingMessageStoreModelInterface&Model
    {
        $this->attributes['session_id'] = $session->getId();

        return $this;
    }

    public function fillContext(MessageContextInterface $context): PendingMessageStoreModelInterface&Model
    {
        $this->attributes['channel_name'] = $context->getChannelName();
        $this->attributes['channel_context'] = $this->makeChannelContextKey($context);

        return $this;
    }
}
