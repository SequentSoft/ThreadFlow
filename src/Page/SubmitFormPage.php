<?php

namespace SequentSoft\ThreadFlow\Page;

use SequentSoft\ThreadFlow\Contracts\Forms\FormInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular\IncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\Regular\OutgoingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;
use SequentSoft\ThreadFlow\Keyboard\Button;
use SequentSoft\ThreadFlow\Messages\Incoming\Regular\FormResultIncomingMessage;
use SequentSoft\ThreadFlow\Messages\Outgoing\Regular\HtmlOutgoingMessage;
use SequentSoft\ThreadFlow\Page\Traits\ConfirmableCancelTrait;

class SubmitFormPage extends AbstractPage
{
    use ConfirmableCancelTrait;

    public function __construct(
        protected FormInterface $form,
        protected PageInterface $page,
    ) {
    }

    public function isDontDisturb(): bool
    {
        return true;
    }

    public function isTrackingPrev(): bool
    {
        return false;
    }

    protected function buttons(): array
    {
        return [
            Button::text($this->form->getConfirmButtonText(), '$confirm'),
            Button::back($this->form->getBackButtonText())->autoHandleAnswer(),
            $this->getConfirmableCancelButton($this->form->getCancelButtonText()),
        ];
    }

    public function show(): OutgoingMessageInterface
    {
        $message = $this->form->getConfirmQuestionText() . "\n\n";

        foreach ($this->form->fields() as $field) {
            $caption = $field->getCaption() ?? $field->getKey();
            $value = $this->form->prepareForDisplay($field, $this->form->getValue($field->getKey()));
            $message .= "<b>{$caption}</b>:\n{$value}\n\n";
        }

        return HtmlOutgoingMessage::make($message)
            ->withKeyboard($this->buttons());
    }

    public function answer(IncomingMessageInterface $message)
    {
        if ($result = $this->handleConfirmableCancelAnswer(
            message: $message,
            page: $this->page,
            questionText: $this->form->getCancelQuestionText(),
            confirmText: $this->form->getCancelConfirmButtonText(),
            denyText: $this->form->getCancelDenyButtonText(),
        )) {
            return $result;
        }

        if ($message->isClicked('$confirm')) {
            return new AnswerToPage(
                page: $this->page,
                message: FormResultIncomingMessage::make(
                    form: $this->form,
                    context: $message->getContext(),
                    timestamp: $message->getTimestamp(),
                ),
            );
        }

        return $this->show();
    }
}
