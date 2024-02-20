<?php

namespace SequentSoft\ThreadFlow\Keyboard;

use SequentSoft\ThreadFlow\Contracts\Keyboard\Buttons\BackButtonInterface;
use SequentSoft\ThreadFlow\Contracts\Keyboard\Buttons\ContactButtonInterface;
use SequentSoft\ThreadFlow\Contracts\Keyboard\Buttons\LocationButtonInterface;
use SequentSoft\ThreadFlow\Contracts\Keyboard\Buttons\TextButtonInterface;
use SequentSoft\ThreadFlow\Keyboard\Buttons\BackButton;
use SequentSoft\ThreadFlow\Keyboard\Buttons\ContactButton;
use SequentSoft\ThreadFlow\Keyboard\Buttons\LocationButton;
use SequentSoft\ThreadFlow\Keyboard\Buttons\TextButton;

class Button
{
    public static function text(string $title, ?string $callbackData = null): TextButtonInterface
    {
        return new TextButton($title, $callbackData);
    }

    public static function contact(string $title): ContactButtonInterface
    {
        return new ContactButton($title);
    }

    public static function location(string $title): LocationButtonInterface
    {
        return new LocationButton($title);
    }

    public static function back(string $title, ?string $callbackData = 'back'): BackButtonInterface
    {
        return new BackButton($title, $callbackData);
    }
}
