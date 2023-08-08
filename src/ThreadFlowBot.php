<?php

declare(strict_types=1);

namespace SequentSoft\ThreadFlow;

use Closure;
use Exception;
use SequentSoft\ThreadFlow\Chat\MessageContext;
use SequentSoft\ThreadFlow\Contracts\BotInterface;
use SequentSoft\ThreadFlow\Contracts\Channel\Incoming\IncomingChannelRegistryInterface;
use SequentSoft\ThreadFlow\Contracts\Channel\Outgoing\OutgoingChannelRegistryInterface;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Config\ConfigInterface;
use SequentSoft\ThreadFlow\Contracts\DataFetchers\DataFetcherInterface;
use SequentSoft\ThreadFlow\Contracts\Dispatcher\DispatcherFactoryInterface;
use SequentSoft\ThreadFlow\Contracts\Events\EventBusInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\IncomingMessageInterface as IMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\OutgoingMessageInterface as OMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;
use SequentSoft\ThreadFlow\Contracts\Router\RouterInterface;
use SequentSoft\ThreadFlow\Contracts\Session\PageStateInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionStoreFactoryInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionStoreInterface;
use SequentSoft\ThreadFlow\Exceptions\Channel\ChannelNotConfiguredException;
use SequentSoft\ThreadFlow\Exceptions\Config\InvalidNestedConfigException;
use SequentSoft\ThreadFlow\Page\PendingDispatchPage;
use SequentSoft\ThreadFlow\Session\PageState;

class ThreadFlowBot implements BotInterface
{
    protected array $outgoingCallbacks = [];

    protected array $incomingCallbacks = [];

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

    /**
     * @param class-string $event
     * @param callable $callback
     * @return void
     */
    public function on(string $event, callable $callback): void
    {
        $this->eventBus->listen($event, $callback);
    }

    /**
     * @throws ChannelNotConfiguredException
     * @throws Exception
     */
    public function showPage(
        string $channelName,
        MessageContextInterface|string $context,
        string $pageClass,
        array $pageAttributes = []
    ): void {
        if (is_string($context)) {
            $context = MessageContext::createFromIds($context, $context);
        }

        $session = $this->getSessionStore($channelName)
            ->load($context);

        $session->setPageState(
            PageState::create($pageClass, $pageAttributes)
        );

        $outgoingChannel = $this->outgoingChannelRegistry
            ->get($channelName, $this->getChannelConfig($channelName));

        $pendingDispatch = $this->makePendingPage($session->getPageState(), $session, $context, null);

        $pendingDispatch->dispatch(
            null,
            fn(OMessageInterface $message, ?PageInterface $contextPage) => $this->processOutgoingMessage(
                $message,
                $channelName,
                $session,
                $contextPage,
                fn(
                    OMessageInterface $message,
                    SessionInterface $session,
                    ?PageInterface $contextPage
                ) => $outgoingChannel->send($message, $session, $contextPage)
            )
        );

        $session->save();
    }

    /**
     * @throws ChannelNotConfiguredException
     */
    protected function getSessionStore(string $channelName): SessionStoreInterface
    {
        return $this->sessionStoreFactory->make(
            $this->getChannelConfig($channelName)
                ->get('session', 'array'),
            $channelName,
            $this->getChannelConfig($channelName)
        );
    }

    /**
     * @throws ChannelNotConfiguredException
     */
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

    /**
     * @throws InvalidNestedConfigException
     */
    public function getAvailableChannels(): array
    {
        return array_keys($this->config->getNested('channels')->all());
    }

    /**
     * @param class-string<PageInterface> $channelName
     * @param IMessageInterface $message
     * @param ?Closure(IMessageInterface, SessionInterface):IMessageInterface $incomingCallback
     * @param ?Closure(OMessageInterface, SessionInterface, PageInterface):OMessageInterface $outgoingCallback
     * @throws ChannelNotConfiguredException
     * @throws Exception
     */
    public function process(
        string $channelName,
        IMessageInterface $message,
        ?Closure $incomingCallback = null,
        ?Closure $outgoingCallback = null,
    ): void {
        $session = $this->getSessionStore($channelName)
            ->load($message->getContext());

        $message = $this->processIncomingMessage($message, $channelName, $session, $incomingCallback);

        $pageState = $this->router->getCurrentPageState(
            $message,
            $session,
            $this->getDefaultEntryPoint($channelName),
        );

        $pendingDispatchPage = $this->makePendingPage($pageState, $session, $message->getContext(), $message);

        $pendingDispatchPage->dispatch(
            null,
            fn(OMessageInterface $message, ?PageInterface $contextPage) => $this->processOutgoingMessage(
                $message,
                $channelName,
                $session,
                $contextPage,
                $outgoingCallback
            )
        );

        $session->save();
    }

    /**
     * @param class-string<PageInterface> $channelName
     * @param IMessageInterface $message
     * @param ?Closure(IMessageInterface, SessionInterface):IMessageInterface $incomingCallback
     * @param ?Closure(OMessageInterface, SessionInterface, PageInterface):OMessageInterface $outgoingCallback
     * @throws ChannelNotConfiguredException
     */
    public function dispatch(
        string $channelName,
        IMessageInterface $message,
        ?Closure $incomingCallback = null,
        ?Closure $outgoingCallback = null,
    ): void {
        $dispatcherName = $this->getChannelConfig($channelName)
            ->get('dispatcher', 'sync');

        $this->dispatcherFactory->make($dispatcherName, $this)
            ->dispatch($channelName, $message, $incomingCallback, $outgoingCallback);
    }

    /**
     * @param class-string<PageInterface> $channelName
     * @param IMessageInterface $message
     * @param ?Closure(IMessageInterface, SessionInterface):IMessageInterface $incomingCallback
     * @param ?Closure(OMessageInterface, SessionInterface, PageInterface):OMessageInterface $outgoingCallback
     */
    public function dispatchSync(
        string $channelName,
        IMessageInterface $message,
        ?Closure $incomingCallback = null,
        ?Closure $outgoingCallback = null,
    ): void {
        $this->dispatcherFactory->make('sync', $this)
            ->dispatch($channelName, $message, $incomingCallback, $outgoingCallback);
    }

    /**
     * @param string $dispatcherMethod
     * @param string $channelName
     * @param DataFetcherInterface $dataFetcher
     * @param ?Closure(IMessageInterface):IMessageInterface $beforeDispatchCallback
     * @param ?Closure(OMessageInterface, SessionInterface, PageInterface):OMessageInterface $outgoingCallback
     * @return void
     * @throws ChannelNotConfiguredException
     */
    protected function listenUsingDispatcher(
        string $dispatcherMethod,
        string $channelName,
        DataFetcherInterface $dataFetcher,
        ?Closure $beforeDispatchCallback = null,
        ?Closure $outgoingCallback = null
    ): void {

        $config = $this->getChannelConfig($channelName);
        $outgoingChannel = $this->outgoingChannelRegistry->get($channelName, $config);
        $incomingChannel = $this->incomingChannelRegistry->get($channelName, $config);

        $incomingChannel->listen(
            $dataFetcher,
            function (IMessageInterface $message) use (
                $dispatcherMethod,
                $outgoingCallback,
                $beforeDispatchCallback,
                $channelName,
                $outgoingChannel,
                $incomingChannel
            ) {
                if ($beforeDispatchCallback) {
                    $beforeDispatchCallback($message);
                }

                $this->$dispatcherMethod(
                    $channelName,
                    $message,
                    fn(IMessageInterface $message, SessionInterface $session) => $incomingChannel
                        ->preprocess($message, $session),

                    function (
                        OMessageInterface $message,
                        SessionInterface $session,
                        ?PageInterface $contextPage
                    ) use ($outgoingChannel, $outgoingCallback) {
                        if ($outgoingCallback) {
                            $outgoingCallback($message, $session, $contextPage);
                        }

                        $outgoingChannel->send($message, $session, $contextPage);
                    },
                );
            }
        );
    }

    /**
     * @param string $channelName
     * @param DataFetcherInterface $dataFetcher
     * @param ?Closure(IMessageInterface):IMessageInterface $beforeDispatchCallback
     * @param ?Closure(OMessageInterface, SessionInterface, PageInterface):OMessageInterface $outgoingCallback
     * @return void
     * @throws ChannelNotConfiguredException
     */
    public function listen(
        string $channelName,
        DataFetcherInterface $dataFetcher,
        ?Closure $beforeDispatchCallback = null,
        ?Closure $outgoingCallback = null
    ): void {
        $this->listenUsingDispatcher(
            'dispatch',
            $channelName,
            $dataFetcher,
            $beforeDispatchCallback,
            $outgoingCallback
        );
    }

    /**
     * @param string $channelName
     * @param DataFetcherInterface $dataFetcher
     * @param ?Closure(IMessageInterface):IMessageInterface $beforeDispatchCallback
     * @param ?Closure(OMessageInterface, SessionInterface, PageInterface):OMessageInterface $outgoingCallback
     * @return void
     * @throws ChannelNotConfiguredException
     */
    public function listenSync(
        string $channelName,
        DataFetcherInterface $dataFetcher,
        ?Closure $beforeDispatchCallback = null,
        ?Closure $outgoingCallback = null
    ): void {
        $this->listenUsingDispatcher(
            'dispatchSync',
            $channelName,
            $dataFetcher,
            $beforeDispatchCallback,
            $outgoingCallback
        );
    }

    /**
     * @throws ChannelNotConfiguredException
     */
    protected function getDefaultEntryPoint(string $channelName): string
    {
        return $this->getChannelConfig($channelName)
            ->get('entry');
    }

    protected function processIncomingMessage(
        IMessageInterface $message,
        string $channelName,
        SessionInterface $session,
        ?Closure $incomingCallback = null
    ): IMessageInterface {
        foreach ($this->incomingCallbacks[$channelName] ?? [] as $callback) {
            $message = $callback($message, $session) ?? $message;
        }

        if ($incomingCallback) {
            $message = $incomingCallback($message, $session) ?? $message;
        }

        return $message;
    }

    protected function makePendingPage(
        PageStateInterface $pageState,
        SessionInterface $session,
        MessageContextInterface $messageContext,
        ?IMessageInterface $message,
    ): PendingDispatchPage {
        $pendingDispatchPage = new PendingDispatchPage(
            $pageState,
            $session,
            $messageContext,
            $message,
        );

        $pendingDispatchPage->withBreadcrumbs();

        return $pendingDispatchPage;
    }

    protected function processOutgoingMessage(
        OMessageInterface $message,
        string $channelName,
        SessionInterface $session,
        ?PageInterface $contextPage = null,
        ?Closure $outgoingCallback = null
    ): OMessageInterface {
        foreach ($this->outgoingCallbacks[$channelName] ?? [] as $callback) {
            $message = $callback($message, $session, $contextPage) ?? $message;
        }

        return $outgoingCallback
            ? ($outgoingCallback($message, $session, $contextPage) ?? $message)
            : $message;
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
