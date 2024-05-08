<?php

namespace SequentSoft\ThreadFlow\Contracts\Forms;

use Closure;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\Regular\OutgoingMessageInterface;

interface FormFieldInterface
{
    public function rules(array $rules, array $messages = []): static;

    public function caption(?string $caption): static;

    public function description(string|OutgoingMessageInterface|null $description): static;

    public function disable($isDisabled = true): static;

    public function isDisabled(): bool;

    public function onChange(Closure $callback): static;

    public function getOnChangeCallback(): ?Closure;

    public function emptyButtonText(string $text): static;

    public function dontChangeButtonText(string $text): static;

    public function getCaption(): ?string;

    public function getDescription(): string|OutgoingMessageInterface|null;

    public function getValidationRules(): array;

    public function getValidationMessages(): array;

    public function getValidationAttributes(): array;

    public function getKey(): string;

    public function getEmptyButtonText(): ?string;

    public function getDontChangeButtonText(): ?string;
}
