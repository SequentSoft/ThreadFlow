<?php

namespace SequentSoft\ThreadFlow\Page\Traits;

use SequentSoft\ThreadFlow\Contracts\Keyboard\Buttons\TextButtonInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular\IncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;
use SequentSoft\ThreadFlow\Keyboard\Button;
use SequentSoft\ThreadFlow\Messages\Outgoing\Regular\TextOutgoingMessage;

trait ConfirmableCancelTrait
{
    protected function getConfirmableCancelButton(string $text): TextButtonInterface
    {
        return Button::text($text, '$cancel');
    }

    protected function handleConfirmableCancelAnswer(
        IncomingMessageInterface $message,
        PageInterface $page,
        string $questionText = 'Are you sure you want to cancel?',
        string $confirmText = 'Yes',
        string $denyText = 'No',
    ): mixed {
        if ($message->isClicked('$cancel')) {
            return TextOutgoingMessage::make($questionText)->withKeyboard([
                Button::text($confirmText, '$cancelConfirm'),
                Button::text($denyText, '$cancelDeny'),
            ]);
        }

        if ($message->isClicked('$cancelConfirm')) {
            return $page;
        }

        if ($message->isClicked('$cancelDeny')) {
            return $this->show();
        }

        return null;
    }
}
