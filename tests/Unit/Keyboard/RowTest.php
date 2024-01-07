<?php

use SequentSoft\ThreadFlow\Contracts\Keyboard\RowInterface;
use SequentSoft\ThreadFlow\Keyboard\Button;
use SequentSoft\ThreadFlow\Keyboard\Row;

it('can be created', function () {
    $row = Row::createFromArray([
        'text' => 'text 1',
        Button::text('text 2'),
    ]);

    expect($row)->toBeInstanceOf(RowInterface::class);
    expect($row->getButtons())->toHaveCount(2);
});
