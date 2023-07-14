<?php

namespace SequentSoft\ThreadFlow\Keyboard;

use SequentSoft\ThreadFlow\Contracts\Keyboard\RowInterface;

class Row implements RowInterface
{
    final public function __construct(
        protected array $buttons,
    ) {
    }

    public function getButtons(): array
    {
        return $this->buttons;
    }

    public static function createFromArray(array $row): RowInterface
    {
        $buttons = [];
        foreach ($row as $callbackData => $buttonText) {
            $buttons[] = new Button($buttonText, $callbackData);
        }
        return new static($buttons);
    }
}
