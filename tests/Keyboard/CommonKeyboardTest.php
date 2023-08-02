<?php

use SequentSoft\ThreadFlow\Contracts\Keyboard\CommonKeyboardInterface;
use SequentSoft\ThreadFlow\Keyboard\CommonKeyboard;

it('can be created', function () {
    $keyboard = CommonKeyboard::createFromArray([
        ['text' => 'text'],
        ['callback_data' => 'payload'],
    ]);

    expect($keyboard)->toBeInstanceOf(CommonKeyboardInterface::class);
    expect($keyboard->getRows())->toHaveCount(2);
});

it('can be created with one time', function () {
    $keyboard = CommonKeyboard::createFromArray([
        ['text' => 'text'],
        ['callback_data' => 'payload'],
    ]);

    $keyboard->oneTimeKeyboard();

    expect($keyboard->isOneTime())->toBeTrue();
});
