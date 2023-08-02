<?php

use SequentSoft\ThreadFlow\Channel\Outgoing\OutgoingChannelRegistry;
use SequentSoft\ThreadFlow\Chat\MessageContext;
use SequentSoft\ThreadFlow\Config;
use SequentSoft\ThreadFlow\Contracts\BotInterface;
use SequentSoft\ThreadFlow\Contracts\Channel\Outgoing\OutgoingChannelInterface;
use SequentSoft\ThreadFlow\Contracts\Config\ConfigInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\IncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\OutgoingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;
use SequentSoft\ThreadFlow\Messages\Incoming\Regular\TextIncomingRegularMessage;
use SequentSoft\ThreadFlow\Router\StatefulPageRouter;
use SequentSoft\ThreadFlow\Session\ArraySessionStore;
use SequentSoft\ThreadFlow\Session\ArraySessionStoreStorage;
use SequentSoft\ThreadFlow\Session\SessionStoreFactory;
use SequentSoft\ThreadFlow\ThreadFlowBot;

beforeEach(function () {
    $this->makeBot = function (string $entry) {
        $config = new Config([
            'channels' => [
                'test-channel' => [
                    'driver' => 'test-channel-driver',
                    'session' => 'test-session-array',
                    'entry' => $entry,
                ],
            ],
        ]);

        $sessionStoreFactory = new SessionStoreFactory();
        $sessionStoreFactory->register('test-session-array', function (string $channelName) {
            return new ArraySessionStore(
                $channelName,
                new ArraySessionStoreStorage()
            );
        });


        $router = new StatefulPageRouter();
        $outgoingChannelRegistry = new OutgoingChannelRegistry();

        $outgoingChannelRegistry->register('test-channel-driver', function (ConfigInterface $config) {
            return new class ($config) implements OutgoingChannelInterface {
                public function __construct(protected ConfigInterface $config)
                {
                }

                public function getConfig(): ConfigInterface
                {
                    return $this->config;
                }

                public function send(
                    OutgoingMessageInterface $message,
                    SessionInterface $session,
                    ?PageInterface $contextPage = null
                ): OutgoingMessageInterface {
                    return $message;
                }
            };
        });

        return new ThreadFlowBot(
            $config,
            $sessionStoreFactory,
            $router,
            $outgoingChannelRegistry
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

    $spy->shouldReceive('incomingMessage')
        ->andReturnUsing(fn($argument) => $argument)
        ->twice();

    $spy->shouldNotReceive('outgoingMessage')
        ->andReturnUsing(fn($argument) => $argument);

    $bot->incoming(
        'test-channel',
        fn(IncomingMessageInterface $message): IncomingMessageInterface => $spy->incomingMessage($message),
    );

    $bot->outgoing(
        'test-channel',
        fn(OutgoingMessageInterface $message): OutgoingMessageInterface => $spy->outgoingMessage($message),
    );

    $bot->process(
        'test-channel',
        $message,
        fn(IncomingMessageInterface $message): IncomingMessageInterface => $spy->incomingMessage($message),
        fn(OutgoingMessageInterface $message): OutgoingMessageInterface => $spy->outgoingMessage($message)
    );
});

it('can process incoming outgoing message', function () {
    $bot = call_user_func($this->makeBot, \Tests\Stubs\AnswerPage::class);

    $messageContext = MessageContext::createFromIds('participant-id', 'room-id');

    $message = new TextIncomingRegularMessage(
        'test-message-id',
        $messageContext,
        new DateTimeImmutable(),
        'test message text'
    );

    $spy = Mockery::spy();

    $spy->shouldReceive('outgoingMessage')
        ->andReturnUsing(fn($argument) => $argument)
        ->twice();

    $bot->outgoing(
        'test-channel',
        fn(OutgoingMessageInterface $message): OutgoingMessageInterface => $spy->outgoingMessage($message),
    );

    $bot->process(
        channelName: 'test-channel',
        message: $message,
        outgoingCallback: fn(OutgoingMessageInterface $message): OutgoingMessageInterface => $spy->outgoingMessage(
            $message
        )
    );
});

it('can get channel config', function () {
    $bot = call_user_func($this->makeBot, \Tests\Stubs\EmptyPage::class);

    $config = $bot->getChannelConfig('test-channel');

    expect($config)->toBeInstanceOf(ConfigInterface::class);
    expect($config->get('driver'))->toBe('test-channel-driver');
    expect($config->get('session'))->toBe('test-session-array');
    expect($config->get('entry'))->toBe(\Tests\Stubs\EmptyPage::class);
});

it('can\'t get channel config for unknown channel', function () {
    $bot = call_user_func($this->makeBot, \Tests\Stubs\EmptyPage::class);

    $bot->getChannelConfig('unknown-channel');
})->throws(\SequentSoft\ThreadFlow\Exceptions\Channel\ChannelNotConfiguredException::class);

it('can show page', function () {
    $bot = call_user_func($this->makeBot, \Tests\Stubs\EmptyPage::class);

    $messageContext = MessageContext::createFromIds('participant-id', 'room-id');

    $message = new TextIncomingRegularMessage(
        'test-message-id',
        $messageContext,
        new DateTimeImmutable(),
        'test message text'
    );

    $spy = Mockery::spy();

    $spy->shouldReceive('outgoingMessage')
        ->andReturnUsing(fn($argument) => $argument)
        ->once();

    $bot->outgoing(
        'test-channel',
        fn(OutgoingMessageInterface $message): OutgoingMessageInterface => $spy->outgoingMessage($message),
    );

    $bot->showPage(
        channelName: 'test-channel',
        context: 'participant-id',
        pageClass: \Tests\Stubs\AnswerPage::class
    );
});

it('can get available channels', function () {
    $bot = call_user_func($this->makeBot, \Tests\Stubs\EmptyPage::class);

    $channels = $bot->getAvailableChannels();


    expect($channels)->toBeArray();
    expect($channels)->toHaveCount(1);
    expect($channels)->toEqual(['test-channel']);
});
