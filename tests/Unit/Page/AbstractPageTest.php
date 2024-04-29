<?php

use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Events\EventBusInterface;
use SequentSoft\ThreadFlow\Contracts\Keyboard\ButtonInterface;
use SequentSoft\ThreadFlow\Contracts\Keyboard\SimpleKeyboardInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular\TextIncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\Regular\TextOutgoingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Page\ActivePagesRepositoryInterface;
use SequentSoft\ThreadFlow\Contracts\Page\DontDisturbPageInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;
use SequentSoft\ThreadFlow\Events\Page\PageHandleRegularMessageEvent;
use SequentSoft\ThreadFlow\Events\Page\PageHandleServiceMessageEvent;
use SequentSoft\ThreadFlow\Events\Page\PageShowEvent;
use SequentSoft\ThreadFlow\Keyboard\Button;
use SequentSoft\ThreadFlow\Messages\Incoming\Regular\TextIncomingMessage;
use SequentSoft\ThreadFlow\Messages\Incoming\Service\NewParticipantIncomingMessage;
use SequentSoft\ThreadFlow\Messages\Outgoing\Regular\TextOutgoingMessage;
use SequentSoft\ThreadFlow\Page\AbstractPage;

beforeEach(function () {
    $this->messageContext = Mockery::mock(MessageContextInterface::class);
    $this->session = Mockery::mock(SessionInterface::class);
    $this->activePagesRepository = Mockery::mock(ActivePagesRepositoryInterface::class);
    $this->eventBus = Mockery::mock(EventBusInterface::class);
    $this->outgoingCallbackMock = Mockery::spy();

    $this->executePage = function (PageInterface $page, $message = false) {
        $page->setContext($this->messageContext);
        $page->setSession($this->session);
        $page->setActivePagesRepository($this->activePagesRepository);

        return expect(
            $page->execute(
                eventBus: $this->eventBus,
                message: $message === false ? TextIncomingMessage::make('test text') : $message,
                callback: function ($message) {
                    $this->outgoingCallbackMock->call($message);

                    return $message;
                }
            )
        );
    };
});

it('can handle text message', function () {
    $this->eventBus->shouldReceive('fire')
        ->once()
        ->with(PageHandleRegularMessageEvent::class);

    $this->outgoingCallbackMock->shouldNotHaveReceived('call');

    ($this->executePage)(
        page: new class () extends AbstractPage {
            public function answer(TextIncomingMessageInterface $message): void
            {
                expect($message->getText())->toBe('test text');
            }
        },
    )->toBeNull();
});

it('can handle service message', function () {
    $this->eventBus->shouldReceive('fire')
        ->once()
        ->with(PageHandleServiceMessageEvent::class);

    $this->outgoingCallbackMock->shouldNotHaveReceived('call');

    ($this->executePage)(
        page: new class () extends AbstractPage {
            public function service($message): void
            {
                expect($message)->toBeInstanceOf(NewParticipantIncomingMessage::class);
            }
        },
        message: NewParticipantIncomingMessage::make()
    )->toBeNull();
});

it('can be executed without message', function () {
    $this->eventBus->shouldReceive('fire')
        ->once()
        ->with(PageShowEvent::class);

    $this->outgoingCallbackMock->shouldNotHaveReceived('call');

    ($this->executePage)(
        page: new class () extends AbstractPage {
            public function show(): void
            {
                // do nothing
            }
        },
        message: null
    )->toBeNull();
});

it('can send text answer as string when showing page', function () {
    $this->eventBus->shouldReceive('fire')
        ->once()
        ->with(PageShowEvent::class);

    $this->outgoingCallbackMock->shouldReceive('call')
        ->once()
        ->withArgs(function ($message) {
            expect($message)->toBeInstanceOf(TextOutgoingMessage::class)
                ->and($message->getText())->toBe('hello');

            return true;
        });

    ($this->executePage)(
        page: new class () extends AbstractPage {
            public function show(): string
            {
                return 'hello';
            }
        },
        message: null
    )->toBeNull();
});

it('can send text answer as TextOutgoingMessage when showing page', function () {
    $this->eventBus->shouldReceive('fire')
        ->once()
        ->with(PageShowEvent::class);

    $this->outgoingCallbackMock->shouldReceive('call')
        ->once()
        ->withArgs(function ($message) {
            expect($message)->toBeInstanceOf(TextOutgoingMessage::class)
                ->and($message->getText())->toBe('hello');

            return true;
        });

    ($this->executePage)(
        page: new class () extends AbstractPage {
            public function show(): TextOutgoingMessageInterface
            {
                return TextOutgoingMessage::make('hello');
            }
        },
        message: null
    )->toBeNull();
});

it('can send text answer with keyboard and access latest sent keyboard', function () {
    $this->eventBus->shouldReceive('fire')
        ->once()
        ->with(PageShowEvent::class);

    $this->outgoingCallbackMock->shouldReceive('call')
        ->once()
        ->withArgs(function ($message) {
            expect($message)->toBeInstanceOf(TextOutgoingMessage::class)
                ->and($message->getText())->toBe('hello');

            return true;
        });

    $page = new class () extends AbstractPage {
        public function show(): TextOutgoingMessageInterface
        {
            return TextOutgoingMessage::make('hello', [
                ['login' => 'Login'],
                [Button::back('Back')],
            ]);
        }
    };

    ($this->executePage)(
        page: $page,
        message: null
    )->toBeNull();

    $lastKeyboard = $page->getLastKeyboard();
    $lastKeyboardFirstButton = $lastKeyboard->getButtons()[0];

    expect($page->keepPrevPageReferenceAfterTransition())->toBeTrue()
        ->and($lastKeyboard)
        ->toBeInstanceOf(SimpleKeyboardInterface::class)
        ->and($lastKeyboardFirstButton)
        ->toBeInstanceOf(ButtonInterface::class)
        ->and($lastKeyboardFirstButton->getTitle())
        ->toBe('Login');
});

it('can make transition to another page', function () {
    $this->eventBus->shouldReceive('fire')
        ->once()
        ->with(PageShowEvent::class);

    $page = new class () extends AbstractPage {
        public function show(): AbstractPage
        {
            return new class () extends AbstractPage {
            };
        }
    };

    ($this->executePage)(
        page: $page,
        message: null
    )->toBeInstanceOf(PageInterface::class);
});

it('can store prev page', function () {
    $prevPage = new class () extends AbstractPage {
    };

    $page = new class () extends AbstractPage {
    };

    $page->setActivePagesRepository($this->activePagesRepository);
    $page->setSession($this->session);
    $page->setContext($this->messageContext);

    expect($page->resolvePrevPage())
        ->toBeNull()
        ->and($page->getPrevPageId())
        ->toBeNull();

    $page->setPrevPageId('prev-page-test-id');

    $this->activePagesRepository->shouldReceive('get')
        ->once()
        ->with($this->messageContext, $this->session, 'prev-page-test-id')
        ->andReturn($prevPage);

    expect($page->resolvePrevPage())
        ->toBe($prevPage)
        ->and($page->getPrevPageId())
        ->toBe('prev-page-test-id');
});

it('can return id', function () {
    $page = new class () extends AbstractPage {
    };

    expect($page->getId())
        ->toBeString();
});

it('can return channel name', function () {
    $this->messageContext->shouldReceive('getChannelName')
        ->once()
        ->andReturn('test-channel');

    $page = new class () extends AbstractPage {
    };

    $page->setContext($this->messageContext);

    expect($page->getChannelName())
        ->toBeString();
});

it('can be in dont disturb state', function () {
    $page1 = new class () extends AbstractPage implements DontDisturbPageInterface {
    };

    expect($page1->isDontDisturb())
        ->toBeTrue();

    $page2 = new class () extends AbstractPage {
    };

    expect($page2->isDontDisturb())
        ->toBeFalse();
});

it('can resolve user', function () {
    $this->eventBus->shouldReceive('fire');
    $this->messageContext->shouldReceive('getUser')
        ->once()
        ->andReturn(['id' => 1, 'name' => 'test']);

    $page = new class () extends AbstractPage {
        public function show(): void
        {
            expect($this->getUser())
                ->toBe(['id' => 1, 'name' => 'test']);
        }
    };

    ($this->executePage)(
        page: $page,
        message: null
    )->toBeNull();
});
