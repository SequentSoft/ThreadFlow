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
     * @return Keyboard
     */
    public static function createFromArray(array $keyboard): static
    {
        $rows = [];

        if (array_is_list($keyboard)) {
            foreach ($keyboard as $row) {
                $rows[] = Row::createFromArray(
                    is_array($row) ? $row : [$row]
                );
            }
        } else {
            foreach ($keyboard as $key => $row) {
                $rows[] = Row::createFromArray(
                    is_array($row) ? $row : [$key => $row]
                );
            }
        }

        return new static($rows);
    }
}
