<?php

use SequentSoft\ThreadFlow\Contracts\Keyboard\ButtonInterface;
use SequentSoft\ThreadFlow\Keyboard\Button;

it('can be created', function () {
    $button = Button::text('text');
    $button->setCallbackData('payload');

    expect($button)->toBeInstanceOf(ButtonInterface::class);
});

it('can be created with text', function () {
    $button = Button::text('text', 'payload');

    expect($button->getTitle())->toBe('text');
    expect($button->getCallbackData())->toBe('payload');
});

it('can be created with contact request', function () {
    $button = Button::contact('text');

    expect($button->getTitle())->toBe('text');
    expect($button->isAnswerAsText())->toBeFalse();
});

it('can be created with location request', function () {
    $button = Button::location('text', 'payload');

    expect($button->getTitle())->toBe('text');
    expect($button->isAnswerAsText())->toBeFalse();
});
