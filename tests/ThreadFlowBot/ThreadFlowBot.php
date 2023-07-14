<?php

use SequentSoft\ThreadFlow\Chat\MessageContext;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\IncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\OutgoingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\Regular\OutgoingRegularMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;
use SequentSoft\ThreadFlow\Exceptions\Channel\ChannelNotConfiguredException;
use SequentSoft\ThreadFlow\Messages\Incoming\Regular\TextIncomingRegularMessage;

it('ThreadFlowBot can get channel config', function () {
    $channelConfigData = [
        'driver' => 'array',
    ];

    $bot = setupBot([
        'channel-1' => $channelConfigData,
    ]);

    expect($channelConfigData)
        ->toBe($bot->getChannelConfig('channel-1')->all());
});

it('ThreadFlowBot can\'t get non existing channel config', function () {
    $bot = setupBot();
    $bot->getChannelConfig('channel-1');
})->throws(ChannelNotConfiguredException::class);

it('ThreadFlowBot can process incoming messages without reply', function () {
    $bot = setupBot([
        'channel-1' => [
            'driver' => 'array',
            'entry' => \Test\EmptyPage::class,
        ],
    ]);

    $incomingMessage = new TextIncomingRegularMessage(
        '1',
        MessageContext::createFromIds('1', '1'),
        new DateTimeImmutable(),
        'text message',
    );

    $spy = Mockery::spy();

    $spy
        ->shouldReceive('incomingCallback')
        ->once()
        ->andReturn($incomingMessage);

    $spy
        ->shouldReceive('outgoingCallback')
        ->never();

    $bot->process(
        'channel-1',
        $incomingMessage,
        fn(IncomingMessageInterface $message, SessionInterface $session) => $spy->incomingCallback($message, $session),
        fn(OutgoingMessageInterface $message, SessionInterface $session) => $spy->outgoingCallback($message, $session),
    );
});


it('ThreadFlowBot can process incoming messages with show page', function () {
    $bot = setupBot([
        'channel-1' => [
            'driver' => 'array',
            'entry' => \Test\PageWithShowText::class,
        ],
    ]);

    $incomingMessage = new TextIncomingRegularMessage(
        '1',
        MessageContext::createFromIds('1', '1'),
        new DateTimeImmutable(),
        'text message',
    );

    $spy = Mockery::spy();

    $spy
        ->shouldReceive('incomingCallback')
        ->once()
        ->andReturn($incomingMessage);

    $spy
        ->shouldReceive('incomingCallbackRegistered')
        ->once()
        ->andReturn($incomingMessage);

    $spy
        ->shouldReceive('outgoingCallback')
        ->andReturnUsing(function (OutgoingMessageInterface $message, SessionInterface $session) {
            expect($message->getText())->toBe('Hello world!');
            return $message;
        })
        ->once();

    $spy
        ->shouldReceive('outgoingCallbackRegistered')
        ->andReturnUsing(function (OutgoingMessageInterface $message, SessionInterface $session) {
            expect($message->getText())->toBe('Hello world!');
            return $message;
        })
        ->once();

    $bot->incoming(
        'channel-1',
        fn(IncomingMessageInterface $message, SessionInterface $session) => $spy->incomingCallbackRegistered(
            $message,
            $session
        )
    );

    $bot->outgoing(
        'channel-1',
        fn(OutgoingMessageInterface $message, SessionInterface $session) => $spy->outgoingCallbackRegistered(
            $message,
            $session
        )
    );

    $bot->process(
        'channel-1',
        $incomingMessage,
        fn(IncomingMessageInterface $message, SessionInterface $session) => $spy->incomingCallback($message, $session),
        fn(OutgoingMessageInterface $message, SessionInterface $session) => $spy->outgoingCallback($message, $session),
    );
});


it('ThreadFlowBot can process page show and answer', function () {
    $bot = setupBot([
        'channel-1' => [
            'driver' => 'array',
            'entry' => \Test\PageWithAnswerText::class,
        ],
    ]);

    $spy = Mockery::spy();

    $spy
        ->shouldReceive('outgoingCallbackShowPage')
        ->andReturnUsing(function (OutgoingMessageInterface $message, SessionInterface $session) {
            expect($message->getText())->toBe('Hello world!');
            return $message;
        })
        ->once();

    $bot->process(
        channelName: 'channel-1',
        message: new TextIncomingRegularMessage(
            '1',
            MessageContext::createFromIds('1', '1'),
            new DateTimeImmutable(),
            'text message',
        ),
        outgoingCallback: fn(
            OutgoingMessageInterface $message,
            SessionInterface $session
        ) => $spy->outgoingCallbackShowPage($message, $session),
    );

    $capturedSession = null;

    $spy
        ->shouldReceive('outgoingCallbackAnswer')
        ->andReturnUsing(
            function (OutgoingRegularMessageInterface $message, SessionInterface $session) use (&$capturedSession) {
                expect($message->getText())->toBe('Answer text');
                expect($message->getKeyboard()->getRows())->toHaveCount(2);
                $capturedSession = $session;
                return $message;
            }
        )
        ->once();

    $bot->process(
        channelName: 'channel-1',
        message: new TextIncomingRegularMessage(
            '1',
            MessageContext::createFromIds('1', '1'),
            new DateTimeImmutable(),
            'next',
        ),
        outgoingCallback: fn(
            OutgoingMessageInterface $message,
            SessionInterface $session
        ) => $spy->outgoingCallbackAnswer($message, $session),
    );

    expect($capturedSession->get('$router:currentPageClass'))->toBe(\Test\EmptyPage::class);
});
