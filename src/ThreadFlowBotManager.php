<?php

declare(strict_types=1);

namespace SequentSoft\ThreadFlow;

use Closure;
use SequentSoft\ThreadFlow\Contracts\BotInterface;
use SequentSoft\ThreadFlow\Contracts\BotManagerInterface;
use SequentSoft\ThreadFlow\Contracts\Channel\Incoming\IncomingChannelInterface;
use SequentSoft\ThreadFlow\Contracts\Channel\Incoming\IncomingChannelRegistryInterface;
use SequentSoft\ThreadFlow\Contracts\Channel\Outgoing\OutgoingChannelInterface;
use SequentSoft\ThreadFlow\Contracts\Channel\Outgoing\OutgoingChannelRegistryInterface;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Config\ConfigInterface;
use SequentSoft\ThreadFlow\Contracts\Dispatcher\DispatcherFactoryInterface;
use SequentSoft\ThreadFlow\Contracts\Dispatcher\DispatcherInterface;
use SequentSoft\ThreadFlow\Contracts\Events\EventBusInterface;
use SequentSoft\ThreadFlow\Contracts\Events\EventInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\IncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Router\RouterInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionStoreFactoryInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionStoreInterface;
use SequentSoft\ThreadFlow\Exceptions\Config\InvalidNestedConfigException;
use Throwable;

class ThreadFlowBotManager implements BotManagerInterface
{
    protected array $channels = [];

    protected array $processingExceptionsHandlers = [];

    public function __construct(
        protected Config $config,
        protected SessionStoreFactoryInterface $sessionStoreFactory,
        protected RouterInterface $router,
        protected OutgoingChannelRegistryInterface $outgoingChannelRegistry,
        protected IncomingChannelRegistryInterface $incomingChannelRegistry,
        protected DispatcherFactoryInterface $dispatcherFactory,
        protected EventBusInterface $eventBus,
    ) {
    }

    public function handleProcessingExceptions(Closure $callback): void
    {
        $this->processingExceptionsHandlers[] = $callback;
    }

    protected function processingException(
        string $channelName,
        Throwable $exception,
        SessionInterface $session,
        MessageContextInterface $messageContext,
        ?IncomingMessageInterface $message = null,
    ): void {
        if (count($this->processingExceptionsHandlers) === 0) {
            throw $exception;
        }

        foreach ($this->processingExceptionsHandlers as $handler) {
            $handler($channelName, $exception, $session, $messageContext, $message);
        }
    }

    public function getConfig(): ConfigInterface
    {
        return $this->config;
    }

    /**
     * @throws InvalidNestedConfigException
     */
    public function getChannelConfig(string $channelName): ConfigInterface
    {
        return $this->config
            ->getNested('channels')
            ->getNested($channelName);
    }

    /**
     * @param class-string<EventInterface> $event
     * @param callable $callback
     * @return void
     */
    public function on(string $event, callable $callback): void
    {
        $this->eventBus->listen($event, $callback);
    }

    /**
     * @throws InvalidNestedConfigException
     */
    protected function getSessionStore(string $channelName): SessionStoreInterface
    {
        $channelConfig = $this->getChannelConfig($channelName);

        return $this->sessionStoreFactory->make(
            $channelConfig->get('session'),
            $channelName,
            $channelConfig
        );
    }

    /**
     * @throws InvalidNestedConfigException
     */
    protected function getOutgoingChannel(string $channelName): OutgoingChannelInterface
    {
        $channelConfig = $this->getChannelConfig($channelName);

        return $this->outgoingChannelRegistry->get(
            $channelConfig->get('driver'),
            $channelConfig
        );
    }

    /**
     * @throws InvalidNestedConfigException
     */
    protected function getIncomingChannel(string $channelName): IncomingChannelInterface
    {
        $channelConfig = $this->getChannelConfig($channelName);

        return $this->incomingChannelRegistry->get(
            $channelConfig->get('driver'),
            $channelConfig
        );
    }

    /**
     * @throws InvalidNestedConfigException
     */
    protected function getDispatcher(string $channelName): DispatcherInterface
    {
        $channelConfig = $this->getChannelConfig($channelName);

        return $this->dispatcherFactory->make(
            $channelConfig->get('dispatcher'),
        );
    }

    /**
     * @throws InvalidNestedConfigException
     */
    public function getAvailableChannels(): array
    {
        return array_keys($this->config->getNested('channels')->all());
    }

    /**
     * @throws InvalidNestedConfigException
     */
    public function channel(string $channelName): BotInterface
    {
        $this->channels[$channelName] ??= new ChannelBot(
            $channelName,
            $this->getChannelConfig($channelName),
            $this->getSessionStore($channelName),
            $this->router,
            $this->getOutgoingChannel($channelName),
            $this->getIncomingChannel($channelName),
            $this->getDispatcher($channelName),
            $this->eventBus->makeChannelEventBus($channelName),
        );

        $this->channels[$channelName]->handleProcessingExceptions(fn (
            Throwable $exception,
            SessionInterface $session,
            MessageContextInterface $messageContext,
            ?IncomingMessageInterface $message = null,
        ) => $this->processingException(
            $channelName,
            $exception,
            $session,
            $messageContext,
            $message,
        ));

        return $this->channels[$channelName];
    }
}