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
        'channel_name',
        'channel_room',
        'data',
        'current_page',
        'dont_disturb_at',
        'expires_at',
    ];

    public $casts = [
        'channel_name' => 'string',
        'channel_room' => 'string',
        'data' => 'string',
        'current_page' => 'string',
        'dont_disturb_at' => 'timestamp',
        'expires_at' => 'timestamp',
    ];

    public function scopeFilterByContext(Builder $query, MessageContextInterface $context): Builder
    {
        return $query->where('channel_name', $context->getChannelName())
            ->where('channel_room', $context->getRoom()->getId())
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
        $this->attributes['data'] = $serializedSession;
        $this->attributes['current_page'] = $session->getPageState()->getPageClass();
        $this->attributes['dont_disturb_at'] = $session->getPageState()->getDontDisturbMarkedAt();

        return $this;
    }

    public function fillContext(MessageContextInterface $context): SessionStoreModelInterface&Model
    {
        $this->attributes['channel_name'] = $context->getChannelName();
        $this->attributes['channel_room'] = $context->getRoom()->getId();

        return $this;
    }
}
