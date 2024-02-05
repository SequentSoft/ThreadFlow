<?php

namespace SequentSoft\ThreadFlow\Keyboard;

use SequentSoft\ThreadFlow\Contracts\Keyboard\InlineKeyboardInterface;
use SequentSoft\ThreadFlow\Contracts\Keyboard\KeyboardInterface;

class InlineKeyboard extends BaseKeyboard implements InlineKeyboardInterface
{
    public static function makeFromKeyboard(KeyboardInterface $keyboard): InlineKeyboardInterface
    {
        return new static($keyboard->getRows());
    }
}
