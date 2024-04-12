<?php

namespace SequentSoft\ThreadFlow\Keyboard;

use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;
use SequentSoft\ThreadFlow\Keyboard\Buttons\BackButton;
use SequentSoft\ThreadFlow\Keyboard\Buttons\ContactButton;
use SequentSoft\ThreadFlow\Keyboard\Buttons\LocationButton;
use SequentSoft\ThreadFlow\Keyboard\Buttons\TextButton;

class Button
{
    public static function text(string $title, ?string $key = null, ?PageInterface $transition = null): TextButton
    {
        if ($key === null) {
            $key = md5($title);
        }

        $button = new TextButton($title, $key);

        if ($transition !== null) {
            $button->autoHandleAnswerPage($transition);
        }

        return $button;
    }

    public static function contact(string $title): ContactButton
    {
        return new ContactButton($title);
    }

    public static function location(string $title): LocationButton
    {
        return new LocationButton($title);
    }

    public static function back(string $title, ?string $callbackData = 'back'): BackButton
    {
        return new BackButton($title, $callbackData);
    }
}
