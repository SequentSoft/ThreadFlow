<?php

namespace SequentSoft\ThreadFlow\Contracts\Keyboard;

interface KeyboardInterface
{
    public function getRows(): array;

    public function row(array $buttons): static;

    public function column(array $buttons): static;
}
