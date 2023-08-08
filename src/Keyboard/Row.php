<?php

namespace SequentSoft\ThreadFlow\Keyboard;

use SequentSoft\ThreadFlow\Contracts\Keyboard\ButtonInterface;
use SequentSoft\ThreadFlow\Contracts\Keyboard\RowInterface;

class Row implements RowInterface
{
    final public function __construct(
        protected array $buttons,
    ) {
    }

    public static function createFromArray(array $row): RowInterface
    {
        $buttons = [];

        $isList = array_is_list($row);

        foreach ($row as $callbackData => $buttonOrText) {
            if ($buttonOrText instanceof ButtonInterface) {
                if (is_null($buttonOrText->getCallbackData())) {
                    $buttonOrText->callbackData($isList ? $buttonOrText->getText() : $callbackData);
                }
                $buttons[] = $buttonOrText;
            } else {
                $buttons[] = Button::text($buttonOrText, $isList ? $buttonOrText : $callbackData);
            }
        }
        return new static($buttons);
    }

    public function getButtons(): array
    {
        return $this->buttons;
    }
}
