<?php

namespace SequentSoft\ThreadFlow\Contracts\Page;

use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;

interface ActivePagesRepositoryInterface
{
    public function put(MessageContextInterface $context, SessionInterface $session, PageInterface $page): void;

    public function getPrevId(MessageContextInterface $context, SessionInterface $session, string $pageId): ?string;

    public function get(MessageContextInterface $context, SessionInterface $session, string $pageId): ?PageInterface;

    public function delete(MessageContextInterface $context, SessionInterface $session, string $pageId): void;
}
