<?php

use SequentSoft\ThreadFlow\Channel\Incoming\CliIncomingChannel;
use SequentSoft\ThreadFlow\Chat\MessageContext;
use SequentSoft\ThreadFlow\Config;
use SequentSoft\ThreadFlow\DataFetchers\InvokableDataFetcher;
use SequentSoft\ThreadFlow\Messages\Incoming\Regular\TextIncomingRegularMessage;
use SequentSoft\ThreadFlow\Session\Session;

it('can be created', function () {

    $messageContext = MessageContext::createFromIds('1', '1');

    $config = new Config([]);

    $channel = new CliIncomingChannel(
        $messageContext,
        $config
    );

    expect($channel)->toBeInstanceOf(CliIncomingChannel::class);
});

it('can listen', function () {

    $messageContext = MessageContext::createFromIds('1', '1');

    $config = new Config([]);

    $channel = new CliIncomingChannel(
        $messageContext,
        $config
    );

    $invokableDataFetcher = new InvokableDataFetcher();

    $answers = [];

    $channel->listen(
        $invokableDataFetcher,
        function ($answer) use (&$answers) {
            $answers[] = $answer;
        }
    );

    $invokableDataFetcher([
        'id' => '1',
        'text' => 'Hello',
    ]);

    expect($answers)->toHaveCount(1);
});

it('can preprocess', function () {

    $messageContext = MessageContext::createFromIds('1', '1');

    $config = new Config([]);

    $channel = new CliIncomingChannel(
        $messageContext,
        $config
    );

    $pageState = \SequentSoft\ThreadFlow\Session\PageState::create(
        \Tests\Stubs\EmptyPage::class,
    );

    $message = new TextIncomingRegularMessage(
        '1',
        $messageContext,
        new DateTimeImmutable(),
        'Hello',
    );

    $preprocessedMessage = $channel->preprocess(
        $message,
        new Session(),
        $pageState
    );

    expect($preprocessedMessage)->toBeInstanceOf(TextIncomingRegularMessage::class);
});
