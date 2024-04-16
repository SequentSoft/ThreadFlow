<?php

namespace SequentSoft\ThreadFlow\Laravel\Models;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;
use SequentSoft\ThreadFlow\Laravel\Contracts\ActivePages\ActivePageStoreModelInterface;

class ThreadFlowActivePage extends Model implements ActivePageStoreModelInterface
{
    public $fillable = [
        'session_id',
        'channel_name',
        'channel_context',
        'page_id',
        'prev_page_id',
        'data',
    ];

    public $casts = [
        'session_id' => 'string',
        'channel_name' => 'string',
        'channel_context' => 'string',
        'page_id' => 'string',
        'prev_page_id' => 'string',
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
        Builder $query,
        MessageContextInterface $context,
        SessionInterface $session
    ): Builder {
        return $query->where('channel_name', $context->getChannelName())
            ->where('channel_context', $this->makeChannelContextKey($context))
            ->where('session_id', $session->getId());
    }

    public function getPrevPageId(): ?string
    {
        return $this->attributes['prev_page_id'];
    }

    public function getSerializedPage(): string
    {
        return $this->attributes['data'];
    }

    public function fillPage(string $serializedPage, PageInterface $page): ActivePageStoreModelInterface&Model
    {
        $this->attributes['session_id'] = $page->getSessionId();
        $this->attributes['page_id'] = $page->getId();
        $this->attributes['prev_page_id'] = $page->getPrevPageId();
        $this->attributes['data'] = $serializedPage;

        return $this;
    }

    public function fillContext(MessageContextInterface $context): ActivePageStoreModelInterface&Model
    {
        $this->attributes['channel_name'] = $context->getChannelName();
        $this->attributes['channel_context'] = $this->makeChannelContextKey($context);

        return $this;
    }
}
