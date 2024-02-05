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

    public static function makeFromKeyboard(KeyboardInterface $keyboard): KeyboardInterface
    {
        return new static($keyboard->getRows());
    }

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

    public function row(array $buttons): static
    {
        $this->rows[] = Row::createFromArray($buttons);

        return $this;
    }

    public function column(array $buttons): static
    {
        foreach ($buttons as $button) {
            $this->rows[] = Row::createFromArray([$button]);
        }

        return $this;
    }
}
