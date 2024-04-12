<?php

namespace SequentSoft\ThreadFlow\Laravel\Page;

use SequentSoft\ThreadFlow\Contracts\Forms\FormInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\BasicIncomingMessageInterface;
use Illuminate\Support\Facades\App;
use SequentSoft\ThreadFlow\Page\AbstractPage;
use SequentSoft\ThreadFlow\Page\BaseFormPage;
use Illuminate\Contracts\View\View as ViewContract;

class Page extends AbstractPage
{
    protected function callHandlerMethod(string $method, ?BasicIncomingMessageInterface $message): mixed
    {
        return App::call([$this, $method], [
            'message' => $message,
        ]);
    }

    protected function makeFormPage(FormInterface $form): BaseFormPage
    {
        return new SimpleFormPage($form, $this);
    }

    protected function handleResult(mixed $result): mixed
    {
        if ($result instanceof ViewContract) {
            $keyboard = $result->getData()['keyboard'] ?? null;

            $result = $this->htmlMessage($result->render());

            if ($keyboard) {
                $result->withKeyboard($keyboard);
            }
        }

        return parent::handleResult($result);
    }
}
