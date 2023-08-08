<?php

namespace SequentSoft\ThreadFlow\Keyboard;

use SequentSoft\ThreadFlow\Contracts\Keyboard\KeyboardInterface;

abstract class BaseKeyboard implements KeyboardInterface
{
    final public function __construct(
        protected array $rows,
    ) {
    }

    public function getRows(): array
    {
        return $this->rows;
    }

    /**
     * @param array $keyboard
     * @return Keyboard
     */
    public static function createFromArray(array $keyboard): static
    {
        $rows = [];
        foreach ($keyboard as $row) {
            $rows[] = Row::createFromArray(
                is_array($row) ? $row : [$row]
            );
        }
        return new static($rows);
    }
}
