<?php

namespace SequentSoft\ThreadFlow\Contracts\Forms;

use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\Regular\OutgoingMessageInterface;

interface FormFieldInterface
{
    public function rules(array $rules, array $messages = []): static;

    public function caption(?string $caption): static;

    public function description(string|OutgoingMessageInterface|null $description): static;

    public function emptyButtonText(string $text): static;

    public function dontChangeButtonText(string $text): static;

    public function getCaption(): ?string;

    public function getDescription(): string|OutgoingMessageInterface|null;

    public function getRules(): array;

    public function getMessages(): array;

    public function getKey(): string;

    public function getEmptyButtonText(): ?string;

    public function getDontChangeButtonText(): ?string;
}
