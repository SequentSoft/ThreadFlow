<?php

namespace SequentSoft\ThreadFlow\Laravel\Session;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Config\ConfigInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionStoreInterface;
use SequentSoft\ThreadFlow\Exceptions\Session\SessionSizeLimitExceededException;
use SequentSoft\ThreadFlow\Laravel\Contracts\Session\SessionStoreModelInterface;
use SequentSoft\ThreadFlow\Laravel\Models\ThreadFlowSession;
use SequentSoft\ThreadFlow\Session\Session;

class EloquentSessionStore implements SessionStoreInterface
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

    public function useSession(MessageContextInterface $context, Closure $callback): mixed
    {
        return DB::transaction(function () use ($context, $callback) {
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

            $sessionData = $model->getSerializedSession();

            if ($sessionData) {
                $sessionData = unserialize($sessionData);
            }

            if (is_array($sessionData)) {
                $session = Session::fromArray($sessionData);
            } else {
                $session = new Session();
            }

            $result = $callback($session);

            //            $session->getBackgroundPageStates()
            //                ->truncate($this->getMaxBackgroundPageStates());

            $sessionSize = $this->calculateSize($session);

            if ($sessionSize > $this->getMaxSize()) {
                throw new SessionSizeLimitExceededException(
                    session: $session,
                    size: $sessionSize,
                    limit: $this->getMaxSize()
                );
            }

            $model->fillSession(serialize($session->toArray()), $session);
            $model->save();

            return $result;
        });
    }

    protected function getMaxBackgroundPageStates(): int
    {
        return $this->config->get('session_background_max', 5);
    }

    protected function getMaxSize(): int
    {
        return $this->config->get('session_max_size', 1024 * 1024 * 0.5); // 512 KB by default
    }

    protected function calculateSize(SessionInterface $session): int
    {
        return strlen(serialize($session));
    }
}
