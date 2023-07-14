<?php

namespace SequentSoft\ThreadFlow;

use Closure;
use SequentSoft\ThreadFlow\Contracts\BotInterface;
use SequentSoft\ThreadFlow\Contracts\Config\ConfigInterface;
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

    protected function getSessionStore(string $channelName): SessionStoreInterface
    {
        return $this->sessionStoreFactory->make(
            $this->getChannelConfig($channelName)
                ->get('session', 'array'),
            $channelName,
            $this->getChannelConfig($channelName)
        );
    }

    protected function getDefaultEntryPoint(string $channelName): string
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

    protected function makePendingPage(
        string $pageClass,
        array $attributes,
        SessionInterface $session,
        IncomingMessageInterface $message,
        RouterInterface $router
    ): PendingDispatchPage {
        return new PendingDispatchPage(
            $pageClass,
            $attributes,
            $session,
            $message,
            $router
        );
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

        $this->makePendingPage(
            $pageClassWithAttributes->getPageClass(),
            $pageClassWithAttributes->getAttributes(),
            $session,
            $message,
            $this->router
        )->dispatch(
            fn(OutgoingMessageInterface $message) => $this->processOutgoingMessage(
                $message,
                $channelName,
                $session,
                $outgoingCallback
            )
        );

        $session->close();
    }

    public function incoming(string $channelName, Closure $callback): void
    {
        $this->incomingCallbacks[$channelName][] = $callback;
    }

    public function outgoing(string $channelName, Closure $callback): void
    {
        $this->outgoingCallbacks[$channelName][] = $callback;
    }
}
