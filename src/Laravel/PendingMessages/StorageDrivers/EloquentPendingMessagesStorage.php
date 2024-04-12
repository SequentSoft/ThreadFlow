<?php

namespace SequentSoft\ThreadFlow\Laravel\PendingMessages\StorageDrivers;

use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Config\ConfigInterface;
use SequentSoft\ThreadFlow\Contracts\PendingMessages\PendingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\PendingMessages\PendingMessagesStorageInterface;
use SequentSoft\ThreadFlow\Contracts\Serializers\SerializerInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;
use SequentSoft\ThreadFlow\Laravel\Models\ThreadFlowPendingMessage;

class EloquentPendingMessagesStorage implements PendingMessagesStorageInterface
{
    public function __construct(
        protected ConfigInterface $config,
        protected SerializerInterface $serializer,
    ) {
    }

    protected function getModelClass(): string
    {
        return $this->config->get('model', ThreadFlowPendingMessage::class);
    }

    public function push(
        MessageContextInterface $context,
        SessionInterface $session,
        PendingMessageInterface $message
    ): void {
        $modelClass = $this->getModelClass();

        $model = new $modelClass();
        $model->fillPendingMessage($this->serializer->serialize($message), $message);
        $model->fillSession($session);
        $model->fillContext($context);
        $model->save();
    }

    public function pull(
        MessageContextInterface $context,
        SessionInterface $session
    ): ?PendingMessageInterface {
        $modelClass = $this->getModelClass();

        $model = $modelClass::query()
            ->orderBy('created_at', 'asc')
            ->filterByContextAndSession($context, $session)
            ->first();

        if (! $model) {
            return null;
        }

        $serializedMessage = $model->getSerializedPendingMessage();

        $result = $this->serializer->unserialize($serializedMessage);

        $model->delete();

        return $result;
    }

    public function isEmpty(MessageContextInterface $context, SessionInterface $session): bool
    {
        $modelClass = $this->getModelClass();

        return $modelClass::query()
            ->filterByContextAndSession($context, $session)
            ->count() === 0;
    }
}
