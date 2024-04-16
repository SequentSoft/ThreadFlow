<?php

namespace SequentSoft\ThreadFlow\Page\ActivePages\StorageDrivers;

use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Config\ConfigInterface;
use SequentSoft\ThreadFlow\Contracts\Page\ActivePagesStorageInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;
use SequentSoft\ThreadFlow\Contracts\Serializers\SerializerInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;

class SessionActivePagesStorage implements ActivePagesStorageInterface
{
    public function __construct(
        protected ConfigInterface $config,
        protected SerializerInterface $serializer,
    ) {
    }

    public function put(MessageContextInterface $context, SessionInterface $session, PageInterface $page): void
    {
        $pages = $session->getData()->get('$activePages', []);
        $pages[$page->getId()] = $page;
        $session->getData()->set('$activePages', $pages);
    }

    public function getPrevId(MessageContextInterface $context, SessionInterface $session, string $pageId): ?string
    {
        $pages = $session->getData()->get('$activePages', []);
        $page = $pages[$pageId] ?? null;

        return $page?->getPrevPageId();
    }

    public function get(MessageContextInterface $context, SessionInterface $session, string $pageId): ?PageInterface
    {
        $pages = $session->getData()->get('$activePages', []);
        return $pages[$pageId] ?? null;
    }

    public function delete(MessageContextInterface $context, SessionInterface $session, string $pageId): void
    {
        $pages = $session->getData()->get('$activePages', []);
        unset($pages[$pageId]);
        $session->getData()->set('$activePages', $pages);
    }
}
