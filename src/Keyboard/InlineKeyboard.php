<?php

namespace SequentSoft\ThreadFlow\Keyboard;

use SequentSoft\ThreadFlow\Contracts\Keyboard\InlineKeyboardInterface;
use SequentSoft\ThreadFlow\Contracts\Keyboard\BaseKeyboardInterface;

class InlineKeyboard extends BaseKeyboard implements InlineKeyboardInterface
{
    public static function makeFromKeyboard(BaseKeyboardInterface $keyboard): InlineKeyboardInterface
    {
        return new static($keyboard->getRows());
    }
}
