<?php

use SequentSoft\ThreadFlow\Contracts\Keyboard\SimpleKeyboardInterface;
use SequentSoft\ThreadFlow\Keyboard\Keyboard;

it('can be created', function () {
    $keyboard = Keyboard::createFromArray([
        ['text' => 'text'],
        ['callback_data' => 'payload'],
    ]);

    expect($keyboard)->toBeInstanceOf(SimpleKeyboardInterface::class);
    expect($keyboard->getRows())->toHaveCount(2);
});

it('can be created with one time', function () {
    $keyboard = Keyboard::createFromArray([
        ['text' => 'text'],
        ['callback_data' => 'payload'],
    ]);

    $keyboard->oneTimeKeyboard();

    expect($keyboard->isOneTime())->toBeTrue();
});
