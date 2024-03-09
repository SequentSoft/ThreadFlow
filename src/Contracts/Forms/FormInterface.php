<?php

namespace SequentSoft\ThreadFlow\Contracts\Forms;

use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular\IncomingMessageInterface;

interface FormInterface
{
    public function setValue(string $key, mixed $value): void;

    public function getValue(string $key): mixed;

    public function values(): array;

    public function isEmpty(): bool;

    public function getDescription(): ?string;

    public function prepareForValidation(FormFieldInterface $field, IncomingMessageInterface $message): mixed;

    public function prepareForStore(FormFieldInterface $field, IncomingMessageInterface $message): mixed;

    public function prepareForDisplay(FormFieldInterface $field, mixed $storedValue): ?string;

    public function fields(): array;

    public function getCancelButtonText(): string;

    public function getCancelQuestionText(): string;

    public function getBackButtonText(): string;

    public function getCancelConfirmButtonText(): string;

    public function getCancelDenyButtonText(): string;

    public function getConfirmQuestionText(): string;

    public function getConfirmButtonText(): string;

    public function getCurrentValueText(): string;

    public function getEmptyButtonText(): string;

    public function getDontChangeButtonText(): string;
}
