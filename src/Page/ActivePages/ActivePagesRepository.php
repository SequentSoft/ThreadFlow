<?php

namespace SequentSoft\ThreadFlow\Page\ActivePages;

use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Page\ActivePagesRepositoryInterface;
use SequentSoft\ThreadFlow\Contracts\Page\ActivePagesStorageInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;

class ActivePagesRepository implements ActivePagesRepositoryInterface
{
    public function __construct(
        protected ActivePagesStorageInterface $storage
    ) {
    }

    public function put(MessageContextInterface $context, SessionInterface $session, PageInterface $page): void
    {
        $this->storage->put($context, $session, $page);
    }

    public function getPrevId(MessageContextInterface $context, SessionInterface $session, string $pageId): ?string
    {
        return $this->storage->getPrevId($context, $session, $pageId);
    }

    public function get(MessageContextInterface $context, SessionInterface $session, string $pageId): ?PageInterface
    {
        $page = $this->storage->get($context, $session, $pageId);

        if ($page) {
            $page->setContext($context);
            $page->setSession($session);
            $page->setActivePagesRepository($this);
        }

        return $page;
    }

    public function delete(MessageContextInterface $context, SessionInterface $session, string $pageId): void
    {
        $this->storage->delete($context, $session, $pageId);
    }
}
