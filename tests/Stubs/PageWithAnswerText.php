<?php

namespace Test;

use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular\IncomingRegularMessageInterface;
use SequentSoft\ThreadFlow\Messages\Outgoing\Regular\TextOutgoingRegularMessage;

class PageWithAnswerText extends \SequentSoft\ThreadFlow\Page\AbstractPage
{
    public function show()
    {
        $message = TextOutgoingRegularMessage::make('Hello world!');
        $this->reply($message);
    }

    public function handleMessage(IncomingRegularMessageInterface $message)
    {
        $this->reply(
            TextOutgoingRegularMessage::make(
                'Answer text',
                [
                    ['button-1' => 'Button 1'],
                    ['button-2' => 'Button 2'],
                ]
            )
        );

        if ($message->isText('next')) {
            return $this->next(EmptyPage::class);
        }
    }
}
