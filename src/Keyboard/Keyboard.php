<?php

namespace SequentSoft\ThreadFlow\Keyboard;

use SequentSoft\ThreadFlow\Contracts\Keyboard\KeyboardInterface;

class Keyboard implements KeyboardInterface
{
    public function __construct(
        protected array $rows,
    ) {
    }

    public function getRows(): array
    {
        return $this->rows;
    }

    /**
     * @param string[][] $keyboard
     * @return KeyboardInterface
     */
    public static function createFromArray(array $keyboard): KeyboardInterface
    {
        $rows = [];
        foreach ($keyboard as $row) {
            $rows[] = Row::createFromArray($row);
        }
        return new static($rows);
    }
}
