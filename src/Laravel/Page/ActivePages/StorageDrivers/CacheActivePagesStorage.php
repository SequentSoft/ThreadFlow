<?php

namespace SequentSoft\ThreadFlow\Laravel\Page\ActivePages\StorageDrivers;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Config\ConfigInterface;
use SequentSoft\ThreadFlow\Contracts\Page\ActivePagesStorageInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;
use SequentSoft\ThreadFlow\Contracts\Serializers\SerializerInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;

class CacheActivePagesStorage implements ActivePagesStorageInterface
{
    public function __construct(
        protected ConfigInterface $config,
        protected SerializerInterface $serializer,
    ) {
    }

    protected function getCacheStore(): Repository
    {
        return Cache::store(
            $this->config->get('store')
        );
    }

    protected function makeKeyString(MessageContextInterface $context, SessionInterface $session, string $pageId): string
    {
        $channelName = $context->getChannelName();
        $sessionId = $session->getId();

        return "{$channelName}:{$sessionId}:{$pageId}";
    }

    public function put(MessageContextInterface $context, SessionInterface $session, PageInterface $page): void
    {
        $this->getCacheStore()->put(
            $this->makeKeyString($context, $session, $page->getId()),
            [
                'page' => $this->serializer->serialize($page),
                'prevPageId' => $page->getPrevPageId(),
            ]
        );
    }

    public function getPrevId(MessageContextInterface $context, SessionInterface $session, string $pageId): ?string
    {
        $data = $this->getCacheStore()->get(
            $this->makeKeyString($context, $session, $pageId)
        );

        if (! $data) {
            return null;
        }

        return $data['prevPageId'] ?? null;
    }

    public function get(MessageContextInterface $context, SessionInterface $session, string $pageId): ?PageInterface
    {
        $data = $this->getCacheStore()->get(
            $this->makeKeyString($context, $session, $pageId)
        );

        if (! $data) {
            return null;
        }

        return $this->serializer->unserialize($data['page']);
    }

    public function delete(MessageContextInterface $context, SessionInterface $session, string $pageId): void
    {
        $this->getCacheStore()->forget(
            $this->makeKeyString($context, $session, $pageId)
        );
    }
}
