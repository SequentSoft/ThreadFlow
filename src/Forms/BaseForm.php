<?php

namespace SequentSoft\ThreadFlow\Forms;

use SequentSoft\ThreadFlow\Contracts\Forms\FormFieldInterface;
use SequentSoft\ThreadFlow\Contracts\Forms\FormInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular\IncomingMessageInterface;

abstract class BaseForm implements FormInterface
{
    protected array $values = [];

    public function setValue(string $key, mixed $value): void
    {
        $this->values[$key] = $value;
    }

    public function getValue(string $key): mixed
    {
        return $this->values[$key] ?? null;
    }

    public function values(): array
    {
        return $this->values;
    }

    public function isEmpty(): bool
    {
        return empty($this->values);
    }

    public function getDescription(): ?string
    {
        return null;
    }

    public function prepareForValidation(FormFieldInterface $field, IncomingMessageInterface $message): mixed
    {
        return $message->getText();
    }

    public function prepareForStore(FormFieldInterface $field, IncomingMessageInterface $message): mixed
    {
        return $message->getText();
    }

    public function prepareForDisplay(FormFieldInterface $field, mixed $storedValue): ?string
    {
        if (is_null($storedValue)) {
            return null;
        }

        if (is_string($storedValue)) {
            return $storedValue;
        }

        if (is_numeric($storedValue)) {
            return (string) $storedValue;
        }

        if ($storedValue instanceof IncomingMessageInterface) {
            return $storedValue->getText();
        }

        return null;
    }

    abstract public function fields(): array;

    public function getCancelButtonText(): string
    {
        return 'Cancel';
    }

    public function getCancelQuestionText(): string
    {
        return 'Are you sure you want to cancel the form filling?';
    }

    public function getBackButtonText(): string
    {
        return 'Back';
    }

    public function getCancelConfirmButtonText(): string
    {
        return 'Yes, cancel';
    }

    public function getCancelDenyButtonText(): string
    {
        return 'No, continue';
    }

    public function getConfirmQuestionText(): string
    {
        return 'Are you sure your data is correct?';
    }

    public function getConfirmButtonText(): string
    {
        return 'Yes, correct';
    }

    public function getCurrentValueText(): string
    {
        return 'Current value';
    }

    public function getEmptyButtonText(): string
    {
        return 'Leave empty';
    }

    public function getDontChangeButtonText(): string
    {
        return 'Don\'t change';
    }
}
