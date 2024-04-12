<?php

namespace SequentSoft\ThreadFlow\Laravel\Models;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;
use SequentSoft\ThreadFlow\Laravel\Contracts\Session\SessionStoreModelInterface;

class ThreadFlowSession extends Model implements SessionStoreModelInterface
{
    public $fillable = [
        'session_id',
        'channel_name',
        'channel_context',
        'data',
        'current_page',
        'dont_disturb_at',
        'expires_at',
    ];

    public $casts = [
        'session_id' => 'string',
        'channel_name' => 'string',
        'channel_context' => 'string',
        'data' => 'string',
        'current_page' => 'string',
        'dont_disturb_at' => 'timestamp',
        'expires_at' => 'timestamp',
    ];

    protected function makeChannelContextKey(MessageContextInterface $context): string
    {
        return implode(':', [
            $context->getRoom()->getId(),
            $context->getParticipant()->getId(),
        ]);
    }

    public function scopeFilterByContext(Builder $query, MessageContextInterface $context): Builder
    {
        return $query->where('channel_name', $context->getChannelName())
            ->where('channel_context', $this->makeChannelContextKey($context))
            ->where(function ($query) {
                return $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    public function getSerializedSession(): ?string
    {
        return $this->attributes['data'] ?? null;
    }

    public function fillSession(string $serializedSession, SessionInterface $session): SessionStoreModelInterface&Model
    {
        $page = $session->getCurrentPage();

        $this->attributes['session_id'] = $session->getId();
        $this->attributes['data'] = $serializedSession;
        $this->attributes['current_page'] = $page ? get_class($page) : null;
        $this->attributes['dont_disturb_at'] = $page->isDontDisturb() ? now() : null;

        return $this;
    }

    public function fillContext(MessageContextInterface $context): SessionStoreModelInterface&Model
    {
        $this->attributes['channel_name'] = $context->getChannelName();
        $this->attributes['channel_context'] = $this->makeChannelContextKey($context);

        return $this;
    }
}
