<?php

namespace SequentSoft\ThreadFlow\Keyboard;

use SequentSoft\ThreadFlow\Contracts\Keyboard\ButtonInterface;
use SequentSoft\ThreadFlow\Contracts\Keyboard\Buttons\TextButtonInterface;
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
                if ($buttonOrText instanceof TextButtonInterface && is_null($buttonOrText->getCallbackData())) {
                    $buttonOrText->setCallbackData($isList ? $buttonOrText->getTitle() : $callbackData);
                }
                $buttons[] = $buttonOrText;
            } else {
                $buttons[] = Button::text($buttonOrText, $isList ? $buttonOrText : $callbackData);
            }
        }

        return new static($buttons);
    }

    /**
     * @return array<ButtonInterface>
     */
    public function getButtons(): array
    {
        return $this->buttons;
    }
}
