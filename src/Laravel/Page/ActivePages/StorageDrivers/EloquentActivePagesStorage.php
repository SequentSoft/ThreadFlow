<?php

namespace SequentSoft\ThreadFlow\Laravel\Page\ActivePages\StorageDrivers;

use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Config\ConfigInterface;
use SequentSoft\ThreadFlow\Contracts\Page\ActivePagesStorageInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;
use SequentSoft\ThreadFlow\Contracts\Serializers\SerializerInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;
use SequentSoft\ThreadFlow\Laravel\Models\ThreadFlowActivePage;

class EloquentActivePagesStorage implements ActivePagesStorageInterface
{
    public function __construct(
        protected ConfigInterface $config,
        protected SerializerInterface $serializer,
    ) {
    }

    protected function getModelClass(): string
    {
        return $this->config->get('model', ThreadFlowActivePage::class);
    }

    public function put(MessageContextInterface $context, SessionInterface $session, PageInterface $page): void
    {
        $modelClass = $this->getModelClass();

        $model = $modelClass::query()
            ->filterByContextAndSession($context, $session)
            ->where('page_id', $page->getId())
            ->first();

        if (! $model) {
            $model = new $modelClass();
        }

        $model->fillPage($this->serializer->serialize($page), $page);
        $model->fillContext($context);
        $model->save();
    }

    public function getPrevId(MessageContextInterface $context, SessionInterface $session, string $pageId): ?string
    {
        $modelClass = $this->getModelClass();

        $model = $modelClass::query()
            ->select('prev_page_id')
            ->filterByContextAndSession($context, $session)
            ->where('page_id', $pageId)
            ->first();

        if (! $model) {
            return null;
        }

        return $model->getPrevPageId();
    }

    public function get(MessageContextInterface $context, SessionInterface $session, string $pageId): ?PageInterface
    {
        $modelClass = $this->getModelClass();

        $model = $modelClass::query()
            ->filterByContextAndSession($context, $session)
            ->where('page_id', $pageId)
            ->first();

        if (! $model) {
            return null;
        }

        $serializedPage = $model->getSerializedPage();

        return $this->serializer->unserialize($serializedPage);
    }

    public function delete(MessageContextInterface $context, SessionInterface $session, string $pageId): void
    {
        $modelClass = $this->getModelClass();

        $modelClass::query()
            ->filterByContextAndSession($context, $session)
            ->where('page_id', $pageId)
            ->delete();
    }
}
