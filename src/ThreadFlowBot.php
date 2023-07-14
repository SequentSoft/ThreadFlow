<?php

namespace SequentSoft\ThreadFlow;

use Closure;
use SequentSoft\ThreadFlow\Contracts\BotInterface;
use SequentSoft\ThreadFlow\Contracts\Channel\Incoming\IncomingChannelInterface;
use SequentSoft\ThreadFlow\Contracts\Channel\Incoming\IncomingChannelRegistryInterface;
use SequentSoft\ThreadFlow\Contracts\Channel\Outgoing\OutgoingChannelInterface;
use SequentSoft\ThreadFlow\Contracts\Channel\Outgoing\OutgoingChannelRegistryInterface;
use SequentSoft\ThreadFlow\Contracts\Config\ConfigInterface;
use SequentSoft\ThreadFlow\Contracts\Dispatcher\DispatcherFactoryInterface;
use SequentSoft\ThreadFlow\Contracts\Dispatcher\DispatcherInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\IncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\OutgoingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Router\RouterInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionStoreFactoryInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionStoreInterface;
use SequentSoft\ThreadFlow\Exceptions\Channel\ChannelNotConfiguredException;
use SequentSoft\ThreadFlow\Exceptions\Config\InvalidNestedConfigException;
use SequentSoft\ThreadFlow\Page\PendingDispatchPage;

class ThreadFlowBot implements BotInterface
{
    protected array $outgoingCallbacks = [];

    protected array $incomingCallbacks = [];

    public function __construct(
        protected Config $config,
        protected SessionStoreFactoryInterface $sessionStoreFactory,
        protected RouterInterface $router,
        protected IncomingChannelRegistryInterface $incomingChannelRegistry,
        protected OutgoingChannelRegistryInterface $outgoingChannelRegistry,
        protected DispatcherFactoryInterface $dispatcherFactory,
    ) {
    }

    public function getChannelConfig(string $channelName): ConfigInterface
    {
        try {
            return $this->config
                ->getNested('channels')
                ->getNested($channelName);
        } catch (InvalidNestedConfigException) {
            throw new ChannelNotConfiguredException("Channel {$channelName} is not configured.");
        }
    }

    public function getIncomingChannel(string $channelName): IncomingChannelInterface
    {
        return $this->incomingChannelRegistry
            ->get($channelName, $this->getChannelConfig($channelName));
    }

    public function getOutgoingChannel(string $channelName): OutgoingChannelInterface
    {
        return $this->outgoingChannelRegistry
            ->get($channelName, $this->getChannelConfig($channelName));
    }

    public function getDispatcher(string $channelName): DispatcherInterface
    {
        return $this->dispatcherFactory->make(
            $this->getChannelConfig($channelName)
                ->get('dispatcher', 'sync')
        );
    }

    public function getSessionStore(string $channelName): SessionStoreInterface
    {
        return $this->sessionStoreFactory->make(
            $this->getChannelConfig($channelName)
                ->get('session', 'array'),
            $channelName,
            $this->getChannelConfig($channelName)
        );
    }

    public function getDefaultEntryPoint(string $channelName): string
    {
        return $this->getChannelConfig($channelName)
            ->get('entry');
    }

    protected function processIncomingMessage(
        IncomingMessageInterface $message,
        string $channelName,
        SessionInterface $session,
        ?Closure $incomingCallback = null
    ): IncomingMessageInterface {
        foreach ($this->incomingCallbacks[$channelName] ?? [] as $callback) {
            $message = $callback($message, $session);
        }

        if ($incomingCallback) {
            $message = $incomingCallback($message, $session);
        }

        return $message;
    }

    protected function processOutgoingMessage(
        OutgoingMessageInterface $message,
        string $channelName,
        SessionInterface $session,
        ?Closure $outgoingCallback = null
    ): OutgoingMessageInterface {
        foreach ($this->outgoingCallbacks[$channelName] ?? [] as $callback) {
            $message = $callback($message, $session);
        }

        return $outgoingCallback
            ? $outgoingCallback($message, $session)
            : $message;
    }

    public function process(
        string $channelName,
        IncomingMessageInterface $message,
        ?Closure $incomingCallback = null,
        ?Closure $outgoingCallback = null,
    ): void {
        $session = $this->getSessionStore($channelName)
            ->load($message->getContext());

        $pageClassWithAttributes = $this->router->getCurrentPage(
            $message,
            $session,
            $this->getDefaultEntryPoint($channelName),
        );

        $message = $this->processIncomingMessage($message, $channelName, $session, $incomingCallback);

        $pendingDispatchPage = new PendingDispatchPage(
            $pageClassWithAttributes->getPageClass(),
            $pageClassWithAttributes->getAttributes(),
            $session,
            $message,
            $this->router
        );

        $pendingDispatchPage->dispatch(
            fn (OutgoingMessageInterface $message) =>
                $this->processOutgoingMessage($message, $channelName, $session, $outgoingCallback)
        );

        $session->close();
    }

    public function incoming(string $channelName, Closure $callback): void
    {
        $this->incomingCallbacks[$channelName] = $callback;
    }

    public function outgoing(string $channelName, Closure $callback): void
    {
        $this->outgoingCallbacks[$channelName] = $callback;
    }
}
