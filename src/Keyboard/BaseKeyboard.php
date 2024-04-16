<?php

namespace SequentSoft\ThreadFlow\Keyboard;

use SequentSoft\ThreadFlow\Contracts\Keyboard\BaseKeyboardInterface;
use SequentSoft\ThreadFlow\Contracts\Keyboard\ButtonInterface;
use SequentSoft\ThreadFlow\Contracts\Keyboard\Buttons\ButtonWithCallbackDataInterface;
use SequentSoft\ThreadFlow\Contracts\Keyboard\RowInterface;

abstract class BaseKeyboard implements BaseKeyboardInterface
{
    final public function __construct(
        /**
         * @var array<RowInterface>
         */
        protected array $rows,
    ) {
    }

    /**
     * @return array<ButtonInterface>
     */
    public function getButtons(): array
    {
        $buttons = [];

        foreach ($this->rows as $row) {
            foreach ($row->getButtons() as $button) {
                $buttons[] = $button;
            }
        }

        return $buttons;
    }

    public function getButtonByKey(string $key): ?ButtonWithCallbackDataInterface
    {
        foreach ($this->getButtons() as $button) {
            if ($button instanceof ButtonWithCallbackDataInterface && $button->getCallbackData() === $key) {
                return $button;
            }
        }

        return null;
    }

    public function getButtonByTitle(string $title): ?ButtonInterface
    {
        foreach ($this->getButtons() as $button) {
            if ($button->getTitle() === $title) {
                return $button;
            }
        }

        return null;
    }

    public function getRows(): array
    {
        return $this->rows;
    }

    public static function makeFromKeyboard(BaseKeyboardInterface $keyboard): BaseKeyboardInterface
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
