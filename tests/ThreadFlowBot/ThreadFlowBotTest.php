<?php

use SequentSoft\ThreadFlow\Channel\Outgoing\OutgoingChannelRegistry;
use SequentSoft\ThreadFlow\Chat\MessageContext;
use SequentSoft\ThreadFlow\Config;
use SequentSoft\ThreadFlow\Contracts\BotInterface;
use SequentSoft\ThreadFlow\Contracts\Channel\Incoming\IncomingChannelInterface;
use SequentSoft\ThreadFlow\Contracts\Channel\Outgoing\OutgoingChannelInterface;
use SequentSoft\ThreadFlow\Contracts\Config\ConfigInterface;
use SequentSoft\ThreadFlow\Contracts\DataFetchers\DataFetcherInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\IncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\OutgoingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;
use SequentSoft\ThreadFlow\Contracts\Session\PageStateInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;
use SequentSoft\ThreadFlow\Dispatcher\SyncIncomingDispatcher;
use SequentSoft\ThreadFlow\Events\ChannelEventBus;
use SequentSoft\ThreadFlow\Events\EventBus;
use SequentSoft\ThreadFlow\Messages\Incoming\Regular\TextIncomingRegularMessage;
use SequentSoft\ThreadFlow\Router\StatefulPageRouter;
use SequentSoft\ThreadFlow\Session\ArraySessionStore;
use SequentSoft\ThreadFlow\Session\ArraySessionStoreStorage;
use SequentSoft\ThreadFlow\Session\SessionStoreFactory;
use SequentSoft\ThreadFlow\ChannelBot;

beforeEach(function () {
    $this->makeBot = function (string $entry) {
        $config = new Config([
            'driver' => 'test-channel-driver',
            'session' => 'test-session-array',
            'entry' => $entry,
        ]);

        $router = new StatefulPageRouter();

        $outgoingChannel = new class implements OutgoingChannelInterface {
            public function send(
                OutgoingMessageInterface $message,
                SessionInterface $session,
                ?PageInterface $contextPage = null
            ): OutgoingMessageInterface {
                return $message;
            }
        };

        $incomingChannel = new class implements IncomingChannelInterface {
            public function listen(
                DataFetcherInterface $fetcher,
                Closure $callback
            ): void {
            }

            public function preprocess(
                IncomingMessageInterface $message,
                SessionInterface $session,
                PageStateInterface $pageState,
            ): IncomingMessageInterface {
                return $message;
            }
        };

        $store = new ArraySessionStore(
            'test-channel',
            new ArraySessionStoreStorage()
        );

        $dispatcher = new SyncIncomingDispatcher();

        $eventBus = new ChannelEventBus();

        return new ChannelBot(
            'test-channel',
            $config,
            $store,
            $router,
            $outgoingChannel,
            $incomingChannel,
            $dispatcher,
            $eventBus
        );
    };
});

it('ThreadFlowBot instance can be created', function () {
    $bot = call_user_func($this->makeBot, \Tests\Stubs\EmptyPage::class);
    expect($bot)->toBeInstanceOf(BotInterface::class);
});

it('can process incoming message', function () {
    $bot = call_user_func($this->makeBot, \Tests\Stubs\EmptyPage::class);

    $messageContext = MessageContext::createFromIds('participant-id', 'room-id');

    $message = new TextIncomingRegularMessage(
        'test-message-id',
        $messageContext,
        new DateTimeImmutable(),
        'test message text'
    );

    $spy = Mockery::spy();

    $spy->shouldNotReceive('outgoingMessage')
        ->andReturnUsing(fn($argument) => $argument);

    $bot->process(
        $message,
        fn(OutgoingMessageInterface $message): OutgoingMessageInterface => $spy->outgoingMessage($message)
    );
});
