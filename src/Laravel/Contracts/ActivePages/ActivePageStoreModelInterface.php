<?php

namespace SequentSoft\ThreadFlow\Laravel\Contracts\ActivePages;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;

interface ActivePageStoreModelInterface
{
    public function scopeFilterByContextAndSession(
        Builder $query,
        MessageContextInterface $context,
        SessionInterface $session
    ): Builder;

    public function getPrevPageId(): ?string;

    public function getSerializedPage(): string;

    public function fillPage(string $serializedPage, PageInterface $page): ActivePageStoreModelInterface&Model;

    public function fillContext(MessageContextInterface $context): ActivePageStoreModelInterface&Model;
}
