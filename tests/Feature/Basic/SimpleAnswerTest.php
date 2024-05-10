<?php

namespace Tests\Feature\Basic;

use SequentSoft\ThreadFlow\Channel\TestChannel;
use SequentSoft\ThreadFlow\Config;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular\ClickIncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular\IncomingMessageInterface;
use SequentSoft\ThreadFlow\Dispatcher\FakeDispatcherFactory;
use SequentSoft\ThreadFlow\Events\EventBus;
use SequentSoft\ThreadFlow\Keyboard\Button;
use SequentSoft\ThreadFlow\Messages\Outgoing\Regular\TextOutgoingMessage;
use SequentSoft\ThreadFlow\Page\AbstractPage;
use SequentSoft\ThreadFlow\Page\ActivePages\ActivePagesStorageFactory;
use SequentSoft\ThreadFlow\Page\ActivePages\StorageDrivers\ArrayActivePagesStorage;
use SequentSoft\ThreadFlow\PendingMessages\PendingMessagesStorageFactory;
use SequentSoft\ThreadFlow\PendingMessages\StorageDrivers\ArrayPendingMessagesStorage;
use SequentSoft\ThreadFlow\Serializers\SimpleSerializer;
use SequentSoft\ThreadFlow\Session\Drivers\ArraySessionStore;
use SequentSoft\ThreadFlow\Session\Drivers\ArraySessionStoreStorage;
use SequentSoft\ThreadFlow\Session\SessionStoreFactory;
use SequentSoft\ThreadFlow\Testing\FakeChannelManager;

beforeEach(function () {
    $this->sessionStoreFactory = new SessionStoreFactory(new Config([
        'array' => ['driver' => 'array'],
    ]));

    $this->dispatcherFactory = new FakeDispatcherFactory(new Config([]));

    $this->pendingMessagesStorageFactory = new PendingMessagesStorageFactory(new Config([
        'array' => ['driver' => 'array'],
    ]));

    $this->activePagesStorageFactory = new ActivePagesStorageFactory(new Config([
        'array' => ['driver' => 'array'],
    ]));

    $this->eventBus = new EventBus();

    $this->channelManager = new FakeChannelManager(
        new Config([
            'testChannel' => [
                'driver' => 'test',
                'session' => 'array',
                'active_pages' => 'array',
                'pending_messages' => 'array',
                'dispatcher' => 'sync',
            ],
        ]),
        $this->sessionStoreFactory,
        $this->dispatcherFactory,
        $this->pendingMessagesStorageFactory,
        $this->activePagesStorageFactory,
        $this->eventBus
    );

    $sessionStorage = new ArraySessionStoreStorage();

    $this->sessionStoreFactory->registerDriver('array', function ($config) use ($sessionStorage) {
        return new ArraySessionStore(
            $config,
            new SimpleSerializer(),
            $sessionStorage
        );
    });

    $this->channelManager->registerChannelDriver('test', function (...$dependencies) {
        return new TestChannel(...$dependencies);
    });

    $this->activePagesStorageFactory->registerDriver('array', function ($config) {
        return new ArrayActivePagesStorage($config, new SimpleSerializer());
    });

    $this->pendingMessagesStorageFactory->registerDriver('array', function ($config) {
        return new ArrayPendingMessagesStorage($config, new SimpleSerializer());
    });
});

test('simple answer', function () {
    class SimpleTextAnswerPage extends AbstractPage
    {
        public function show(): string
        {
            return 'Hello';
        }
    }

    $this->channelManager->channel('testChannel')
        ->test()
        ->withPage(new SimpleTextAnswerPage())
        ->input('Hello')
        ->assertOutgoingMessageText('Hello')
        ->assertOutgoingMessagesCount(1);
});

test('simple transition', function () {
    class SimpleWelcomePage extends AbstractPage
    {
        public function answer(IncomingMessageInterface $message)
        {
            return new SecondPage();
        }
    }

    class SecondPage extends AbstractPage
    {
        public function show()
        {
            return TextOutgoingMessage::make('Second page');
        }
    }

    $this->channelManager->channel('testChannel')
        ->test()
        ->withPage(new SimpleWelcomePage())
        ->input('test message')
        ->assertState(SecondPage::class);
});

test('click button', function () {
    class WelcomePageWithButtons extends AbstractPage
    {
        public function show()
        {
            return TextOutgoingMessage::make('Welcome')
                ->withKeyboard([
                    Button::text('Go to next page', 'next'),
                ]);
        }

        public function answer(ClickIncomingMessageInterface $message)
        {
            if ($message->isClicked('next')) {
                return 'some message text';
            }
        }
    }

    $this->channelManager->channel('testChannel')
        ->test()
        ->withPage(new WelcomePageWithButtons())
        ->input('hello')
        ->assertState(WelcomePageWithButtons::class);

    $this->channelManager->channel('testChannel')
        ->test()
        ->click('next')
        ->assertOutgoingMessagesCount(1)
        ->assertOutgoingMessageText('some message text');
});
