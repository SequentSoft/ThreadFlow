<?php

namespace SequentSoft\ThreadFlow\Testing;

use SequentSoft\ThreadFlow\ChannelManager;
use SequentSoft\ThreadFlow\Contracts\Config\ConfigInterface;
use SequentSoft\ThreadFlow\Contracts\Dispatcher\DispatcherFactoryInterface;
use SequentSoft\ThreadFlow\Contracts\Events\EventBusInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionStoreFactoryInterface;
use SequentSoft\ThreadFlow\Dispatcher\FakeDispatcherFactory;
use SequentSoft\ThreadFlow\Traits\TestInputResults;

class FakeChannelManager extends ChannelManager
{
    use TestInputResults;

    protected ResultsRecorder $resultsRecorder;

    public function __construct(
        protected ConfigInterface $config,
        protected SessionStoreFactoryInterface $sessionStoreFactory,
        protected DispatcherFactoryInterface $dispatcherFactory,
        protected EventBusInterface $eventBus,
    ) {
        $this->resultsRecorder = new ResultsRecorder();

        $this->registerTestInputResultListeners($eventBus, $this->resultsRecorder);

        parent::__construct(
            $config,
            $sessionStoreFactory,
            $dispatcherFactory,
            $eventBus,
        );
    }

    protected function getDispatcherFactory(): DispatcherFactoryInterface
    {
        return new FakeDispatcherFactory();
    }

    public function __call(string $name, array $arguments)
    {
        if (str_starts_with($name, 'assert')) {
            return $this->resultsRecorder->$name(...$arguments);
        }

        throw new \BadMethodCallException("Method {$name} does not exist.");
    }
}
