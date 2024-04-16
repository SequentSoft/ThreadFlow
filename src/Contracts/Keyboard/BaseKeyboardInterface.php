<?php

namespace SequentSoft\ThreadFlow\Contracts\Keyboard;

use SequentSoft\ThreadFlow\Contracts\Keyboard\Buttons\ButtonWithCallbackDataInterface;

interface BaseKeyboardInterface
{
    public function getRows(): array;

    public function row(array $buttons): static;

    public function column(array $buttons): static;

    public function getButtons(): array;

    public function getButtonByKey(string $key): ?ButtonWithCallbackDataInterface;

    public function getButtonByTitle(string $title): ?ButtonInterface;
}
