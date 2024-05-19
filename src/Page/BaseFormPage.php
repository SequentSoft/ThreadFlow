<?php

namespace SequentSoft\ThreadFlow\Page;

use SequentSoft\ThreadFlow\Contracts\Forms\FormFieldInterface;
use SequentSoft\ThreadFlow\Contracts\Forms\FormFieldOptionsInterface;
use SequentSoft\ThreadFlow\Contracts\Forms\FormInterface;
use SequentSoft\ThreadFlow\Contracts\Keyboard\Buttons\BackButtonInterface;
use SequentSoft\ThreadFlow\Contracts\Keyboard\Buttons\ButtonWithCallbackDataInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular\IncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\Regular\MarkdownOutgoingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\Regular\TextOutgoingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;
use SequentSoft\ThreadFlow\Keyboard\Button;
use SequentSoft\ThreadFlow\Messages\Incoming\Regular\ClickIncomingMessage;
use SequentSoft\ThreadFlow\Messages\Outgoing\Regular\HtmlOutgoingMessage;
use SequentSoft\ThreadFlow\Messages\Outgoing\Regular\TextOutgoingMessage;
use SequentSoft\ThreadFlow\Page\Traits\ConfirmableCancelTrait;

class BaseFormPage extends AbstractPage
{
    use ConfirmableCancelTrait;

    final public function __construct(
        protected FormInterface $form,
        protected PageInterface $page,
        protected ?string $fieldKey = null,
    ) {
    }

    public function setForm(FormInterface $form): void
    {
        $this->form = $form;
    }

    public function isDontDisturb(): bool
    {
        return true;
    }

    protected function getFirstField(array $fields): ?FormFieldInterface
    {
        return array_values($fields)[0] ?? null;
    }

    protected function isFieldFirst(FormFieldInterface $field, array $fields): bool
    {
        return $field->getKey() === $this->getFirstField($fields)?->getKey();
    }

    protected function getCurrentField(array $fields): ?FormFieldInterface
    {
        if (! $this->fieldKey) {
            return $this->getFirstField($fields);
        }

        foreach ($fields as $field) {
            if ($field->getKey() === $this->fieldKey) {
                return $field;
            }
        }

        return null;
    }

    protected function getNextFieldKey(string $currentFieldKey, array $fields): ?string
    {
        $currentFieldFound = false;

        foreach ($fields as $field) {
            if ($currentFieldFound && ! $field->isDisabled()) {
                return $field->getKey();
            }

            if ($field->getKey() === $currentFieldKey) {
                $currentFieldFound = true;
            }
        }

        return null;
    }

    protected function getAnswers(FormFieldOptionsInterface $field): array
    {
        $options = $field->getOptions();
        $answers = [];

        foreach ($options as $key => $value) {
            $preparedKey = is_string($key) ? $key : (string) $key;
            $answers[] = Button::text($value, $preparedKey);
        }

        return $answers;
    }

    protected function getChosenAnswer(FormFieldInterface $field, IncomingMessageInterface $message): ?string
    {
        if ($field instanceof FormFieldOptionsInterface && $message instanceof ClickIncomingMessage) {
            $options = $field->getOptions();
            $clickedButton = $message->getButton();

            if ($clickedButton instanceof ButtonWithCallbackDataInterface) {
                $clickedKey = $clickedButton->getCallbackData();
                if (array_key_exists($clickedKey, $options)) {
                    return $options[$clickedKey];
                }
            }
        }

        return null;
    }

    protected function getFieldButtons(FormFieldInterface $field, array $fields): array
    {
        $isRequired = in_array('required', $field->getValidationRules());
        $isFirstField = $this->isFieldFirst($field, $fields);
        $hasValue = $this->form->getValue($field->getKey());

        $emptyButtonText = $field->getEmptyButtonText() ?? $this->form->getEmptyButtonText();
        $dontChangeButtonText = $field->getDontChangeButtonText() ?? $this->form->getDontChangeButtonText();
        $backButtonText = $this->form->getBackButtonText();

        if ($field instanceof FormFieldOptionsInterface) {
            $answers = $this->getAnswers($field);
        } else {
            $answers = [];
        }

        return array_filter([
            array_filter([
                $isRequired ? null : Button::text($emptyButtonText, '$empty'),
                $hasValue ? Button::text($dontChangeButtonText, '$dontChange') : null,
            ]),
            ...$answers,
            $isFirstField ? null : Button::back($backButtonText),
            $this->getConfirmableCancelButton($this->form->getCancelButtonText()),
        ]);
    }

    protected function validate(FormFieldInterface $field, IncomingMessageInterface $message): ?string
    {
        throw new \RuntimeException('Validation not available for this form page.');
    }

    public function show(): mixed
    {
        $fields = $this->form->fields($this->getContext());
        $currentField = $this->getCurrentField($fields);

        // if the form is empty and there are no fields, then return the main page
        if ($this->form->isEmpty() && ! $currentField) {
            return $this->page;
        }

        $key = $currentField->getKey();
        $filledValue = $this->form->prepareForDisplay($currentField, $this->form->getValue($key));

        $fieldCaption = $currentField->getCaption() ?? $key;
        $formDescription = $this->form->getDescription();
        $fieldDescription = $currentField->getDescription();
        $currentValueText = $this->form->getCurrentValueText();

        if ($fieldDescription instanceof TextOutgoingMessageInterface) {
            $fieldDescription = $fieldDescription->getText();
        } elseif ($fieldDescription instanceof HtmlOutgoingMessage) {
            $fieldDescription = $fieldDescription->getHtml();
        } elseif ($fieldDescription instanceof MarkdownOutgoingMessageInterface) {
            $fieldDescription = $fieldDescription->getMarkdown();
        } elseif (! is_string($fieldDescription)) {
            $fieldDescription = '';
        }

        $message = implode("\n", array_filter([
            $this->isFieldFirst($currentField, $fields) && $formDescription ? "{$formDescription}\n" : '',
            $fieldCaption ? "<b>{$fieldCaption}</b>\n" : '',
            $fieldDescription,
            $filledValue ? "\n<b>{$currentValueText}</b>:\n{$filledValue}" : '',
        ]));

        return HtmlOutgoingMessage::make($message)
            ->withKeyboard($this->getFieldButtons($currentField, $fields), $fieldCaption);
    }

    protected function prepareValueForValidation(FormFieldInterface $field, IncomingMessageInterface $message): mixed
    {
        if ($message->isClicked('$empty')) {
            return null;
        }

        if ($field instanceof FormFieldOptionsInterface) {
            $chosenAnswer = $this->getChosenAnswer($field, $message);

            if ($chosenAnswer !== null) {
                return $chosenAnswer;
            }
        }

        return $this->form->prepareForValidation($field, $message);
    }

    protected function prepareValueForStore(FormFieldInterface $field, IncomingMessageInterface $message): mixed
    {
        if ($message->isClicked('$empty')) {
            return null;
        }

        if ($field instanceof FormFieldOptionsInterface) {
            $chosenAnswer = $this->getChosenAnswer($field, $message);

            if ($chosenAnswer !== null) {
                return $chosenAnswer;
            }
        }

        return $this->form->prepareForStore($field, $message);
    }

    protected function getNextStep(string $key, array $fields): PageInterface
    {
        if ($nextFieldKey = $this->getNextFieldKey($key, $fields)) {
            return new static($this->form, $this->page, $nextFieldKey);
        }

        return new SubmitFormPage($this->form, $this->page);
    }

    /**
     * Answer to the incoming message
     */
    public function answer(IncomingMessageInterface $message): mixed
    {
        if ($message instanceof ClickIncomingMessage && $message->getButton() instanceof BackButtonInterface) {
            $prev = $this->resolvePrevPage();

            if ($prev instanceof BaseFormPage) {
                $prev->setForm($this->form);
            }

            return $prev;
        }

        if ($result = $this->handleConfirmableCancelAnswer(
            message: $message,
            page: $this->page,
            questionText: $this->form->getCancelQuestionText(),
            confirmText: $this->form->getCancelConfirmButtonText(),
            denyText: $this->form->getCancelDenyButtonText(),
        )) {
            return $result;
        }

        $fields = $this->form->fields($this->getContext());

        // if not field that need to fill, then return the main page
        if (! $currentField = $this->getCurrentField($fields)) {
            return $this->page;
        }

        $key = $currentField->getKey();

        if ($message->isClicked('$dontChange')) {
            return $this->getNextStep($key, $fields);
        }

        // validate selected option or show options again
        if (! $message->isClicked('$empty') && $currentField instanceof FormFieldOptionsInterface) {
            $chosenAnswer = $this->getChosenAnswer($currentField, $message);

            if (! $currentField->isCustomOptionAllowed() && $chosenAnswer === null) {
                return $this->show();
            }
        }

        if ($validationErrorMessage = $this->validate($currentField, $message)) {
            return TextOutgoingMessage::make($validationErrorMessage)
                ->withKeyboard($this->getFieldButtons($currentField, $fields));
        }

        $oldValue = $this->form->getValue($key);
        $newValue = $this->prepareValueForStore($currentField, $message);

        if ($onChangeCallback = $currentField->getOnChangeCallback()) {
            $newValue = $onChangeCallback($newValue, $oldValue, $this->form);
        }

        $this->form->setValue($key, $newValue);

        return $this->getNextStep($key, $fields);
    }
}
