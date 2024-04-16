<?php

namespace SequentSoft\ThreadFlow\Testing;

use SequentSoft\ThreadFlow\ChannelManager;
use SequentSoft\ThreadFlow\Contracts\Config\ConfigInterface;
use SequentSoft\ThreadFlow\Contracts\Dispatcher\DispatcherFactoryInterface;
use SequentSoft\ThreadFlow\Contracts\Events\EventBusInterface;
use SequentSoft\ThreadFlow\Contracts\Page\ActivePagesStorageFactoryInterface;
use SequentSoft\ThreadFlow\Contracts\PendingMessages\PendingMessagesStorageFactoryInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionStoreFactoryInterface;
use SequentSoft\ThreadFlow\Contracts\Testing\ResultsRecorderInterface;
use SequentSoft\ThreadFlow\Traits\TestInputResults;
use SequentSoft\ThreadFlow\Testing\Illuminate\Testing\ResultsRecorder;

class FakeChannelManager extends ChannelManager
{
    use TestInputResults;

    protected ResultsRecorderInterface $resultsRecorder;

    protected DispatcherFactoryInterface $fakeDispatcherFactory;

    public function __construct(
        protected ConfigInterface $config,
        protected SessionStoreFactoryInterface $sessionStoreFactory,
        protected DispatcherFactoryInterface $dispatcherFactory,
        protected PendingMessagesStorageFactoryInterface $pendingMessagesStorageFactory,
        protected ActivePagesStorageFactoryInterface $activePagesStorageFactory,
        protected EventBusInterface $eventBus,
    ) {
        $this->resultsRecorder = new ResultsRecorder();

        $this->registerTestInputResultListeners($eventBus, $this->resultsRecorder);

        parent::__construct(
            $config,
            $sessionStoreFactory,
            $dispatcherFactory,
            $pendingMessagesStorageFactory,
            $activePagesStorageFactory,
            $eventBus,
        );
    }

    public function setFakeDispatcherFactory(DispatcherFactoryInterface $fakeDispatcherFactory): void
    {
        $this->fakeDispatcherFactory = $fakeDispatcherFactory;
    }

    protected function getDispatcherFactory(): DispatcherFactoryInterface
    {
        return $this->fakeDispatcherFactory;
    }

    public function __call(string $name, array $arguments)
    {
        if (str_starts_with($name, 'assert')) {
            return $this->resultsRecorder->$name(...$arguments);
        }

        throw new \BadMethodCallException("Method {$name} does not exist.");
    }
}
