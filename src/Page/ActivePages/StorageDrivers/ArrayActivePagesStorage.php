<?php

namespace SequentSoft\ThreadFlow\Page\ActivePages\StorageDrivers;

use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Config\ConfigInterface;
use SequentSoft\ThreadFlow\Contracts\Page\ActivePagesStorageInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;
use SequentSoft\ThreadFlow\Contracts\Serializers\SerializerInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;

class ArrayActivePagesStorage implements ActivePagesStorageInterface
{
    protected array $activePages = [];

    public function __construct(
        protected ConfigInterface $config,
        protected SerializerInterface $serializer,
    ) {
    }

    protected function makeKey(MessageContextInterface $context, SessionInterface $session, string $pageId): string
    {
        return implode(':', [
            $context->getChannelName(),
            $context->getRoom()->getId(),
            $context->getParticipant()->getId(),
            $session->getId(),
            $pageId,
        ]);
    }

    public function put(MessageContextInterface $context, SessionInterface $session, PageInterface $page): void
    {
        $this->activePages[$this->makeKey($context, $session, $page->getId())] = $this->serializer->serialize($page);
    }

    public function getPrevId(MessageContextInterface $context, SessionInterface $session, string $pageId): ?string
    {
        return $this->get($context, $session, $pageId)?->getPrevPageId();
    }

    public function get(MessageContextInterface $context, SessionInterface $session, string $pageId): ?PageInterface
    {
        $key = $this->makeKey($context, $session, $pageId);

        if (! isset($this->activePages[$key])) {
            return null;
        }

        return $this->serializer->unserialize($this->activePages[$key]);
    }

    public function delete(MessageContextInterface $context, SessionInterface $session, string $pageId): void
    {
        unset($this->activePages[$this->makeKey($context, $session, $pageId)]);
    }
}
