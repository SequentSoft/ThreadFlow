<?php

namespace SequentSoft\ThreadFlow\Contracts\Forms;

interface FormFieldOptionsInterface
{
    public function getOptions(): array;

    public function isCustomOptionAllowed(): bool;
}
