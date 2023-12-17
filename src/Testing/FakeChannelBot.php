<?php

namespace SequentSoft\ThreadFlow\Testing;

use SequentSoft\ThreadFlow\Channel\Outgoing\CallbackOutgoingChannel;
use SequentSoft\ThreadFlow\ChannelBot;
use SequentSoft\ThreadFlow\Chat\MessageContext;
use SequentSoft\ThreadFlow\Contracts\Channel\Incoming\IncomingChannelInterface;
use SequentSoft\ThreadFlow\Contracts\Channel\Outgoing\OutgoingChannelInterface;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Config\SimpleConfigInterface;
use SequentSoft\ThreadFlow\Contracts\Dispatcher\DispatcherInterface;
use SequentSoft\ThreadFlow\Contracts\Events\ChannelEventBusInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\IncomingMessageInterface as IMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\OutgoingMessageInterface as OMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Router\RouterInterface;
use SequentSoft\ThreadFlow\Contracts\Session\PageStateInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionStoreInterface;
use SequentSoft\ThreadFlow\Events\Page\PageHandleRegularMessageEvent;
use SequentSoft\ThreadFlow\Events\Page\PageHandleServiceMessageEvent;
use SequentSoft\ThreadFlow\Events\Page\PageHandleWelcomeMessageEvent;
use SequentSoft\ThreadFlow\Events\Page\PageShowEvent;
use SequentSoft\ThreadFlow\Session\PageState;

class FakeChannelBot extends ChannelBot
{
    protected ?PageStateInterface $withState = null;

    public function __construct(
        protected string $channelName,
        protected SimpleConfigInterface $config,
        protected SessionStoreInterface $sessionStore,
        protected RouterInterface $router,
        protected OutgoingChannelInterface $outgoingChannel,
        protected IncomingChannelInterface $incomingChannel,
        protected DispatcherInterface $dispatcher,
        protected ChannelEventBusInterface $eventBus,
        protected ResultsRecorder $resultsRecorder,
    ) {
        parent::__construct(
            $channelName,
            $config,
            $sessionStore,
            $router,
            $this->getFakeOutgoingChannel(),
            $incomingChannel,
            $dispatcher,
            $eventBus,
        );

        $this->on(PageHandleRegularMessageEvent::class, function (PageHandleRegularMessageEvent $event) {
            $this->resultsRecorder->recordPageHandleRegularMessage($event->getPage(), $event->getMessage());
        });

        $this->on(PageHandleServiceMessageEvent::class, function (PageHandleServiceMessageEvent $event) {
            $this->resultsRecorder->recordPageHandleServiceMessage($event->getPage(), $event->getMessage());
        });

        $this->on(PageHandleWelcomeMessageEvent::class, function (PageHandleWelcomeMessageEvent $event) {
            $this->resultsRecorder->recordPageHandleWelcomeMessage($event->getPage(), $event->getMessage());
        });

        $this->on(PageShowEvent::class, function (PageShowEvent $event) {
            $this->resultsRecorder->recordPageShow($event->getPage());
        });
    }

    protected function createOrGetSession(MessageContextInterface $context, bool $fresh = false): SessionInterface
    {
        $session = parent::createOrGetSession($context, $fresh);

        if ($this->withState !== null) {
            $session->setPageState($this->withState);
            $this->withState = null;
        }

        return $session;
    }

    protected function getFakeOutgoingChannel(): OutgoingChannelInterface
    {
        return new CallbackOutgoingChannel(
            $this->getConfig(),
            function (OMessageInterface $message) {
                $this->resultsRecorder->recordSentOutgoingMessage($message);
                return $message->setId(bin2hex(random_bytes(16)));
            }
        );
    }

    public function setOutgoingChannel(OutgoingChannelInterface $outgoingChannel): void
    {
        $this->outgoingChannel = $this->getFakeOutgoingChannel();
    }

    public function testInput(
        IMessageInterface|string $message,
        ?MessageContextInterface $context = null
    ): ResultsRecorder {
        $globalResultsRecorder = $this->resultsRecorder;
        $this->resultsRecorder = new ResultsRecorder();

        if (is_string($message)) {
            $id = 'test-message-id-' . bin2hex(random_bytes(16));
            $message = $this->incomingChannel->makeMessageFromText(
                $id,
                $message,
                new \DateTimeImmutable(),
                MessageContext::createFromIds('test-participant', 'test-chat')
            );
        }

        if ($context !== null) {
            $message->setContext($context);
        }

        $this->dispatch($message);

        $localResultsRecorder = $this->resultsRecorder;
        $this->resultsRecorder = $globalResultsRecorder;
        $globalResultsRecorder->add($localResultsRecorder);

        return $localResultsRecorder;
    }

    public function withState(string $pageClass, array $attributes): static
    {
        $this->withState = PageState::create($pageClass, $attributes);

        return $this;
    }
}
