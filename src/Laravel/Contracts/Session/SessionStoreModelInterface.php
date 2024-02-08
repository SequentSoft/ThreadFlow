<?php

namespace SequentSoft\ThreadFlow\Laravel\Contracts\Session;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;

interface SessionStoreModelInterface
{
    public function scopeFilterByContext(Builder $query, MessageContextInterface $context): Builder;

    public function getSerializedSession(): ?string;

    public function fillSession(string $serializedSession, SessionInterface $session): SessionStoreModelInterface&Model;

    public function fillContext(MessageContextInterface $context): SessionStoreModelInterface&Model;
}
