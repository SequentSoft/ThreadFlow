<?php

namespace SequentSoft\ThreadFlow\Laravel\Session;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Config\ConfigInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionStoreInterface;
use SequentSoft\ThreadFlow\Laravel\Contracts\Session\SessionStoreModelInterface;
use SequentSoft\ThreadFlow\Laravel\Models\ThreadFlowSession;

class EloquentSessionStore extends BaseSessionStore implements SessionStoreInterface
{
    public function __construct(
        protected string $channelName,
        protected ConfigInterface $config,
    ) {
    }

    protected function getModelClass(): string
    {
        return $this->config->get('model', ThreadFlowSession::class);
    }

    protected function loadModelByContext(MessageContextInterface $context): SessionStoreModelInterface
    {
        /** @var class-string<SessionStoreModelInterface&Model> $modelClass */
        $modelClass = $this->getModelClass();

        $model = $modelClass::query()
            ->lockForUpdate()
            ->filterByContext($context)
            ->first();

        if (! $model) {
            $model = new $modelClass();
            $model->fillContext($context);
        }

        return $model;
    }

    protected function load(SessionStoreModelInterface $model): SessionInterface
    {
        $sessionData = $model->getSerializedSession();

        if ($sessionData) {
            $sessionData = unserialize($sessionData);
        }

        return $this->makeFromData($sessionData);
    }

    public function useSession(MessageContextInterface $context, callable $callback): mixed
    {
        return DB::transaction(function () use ($context, $callback) {

            $model = $this->loadModelByContext($context);
            $session = $this->load($model);

            $result = $this->run($session, $callback);

            $model->fillSession(serialize($session->toArray()), $session);
            $model->save();

            return $result;
        });
    }

    protected function getConfig(): ConfigInterface
    {
        return $this->config;
    }
}
