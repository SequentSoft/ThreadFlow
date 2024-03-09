<?php

namespace SequentSoft\ThreadFlow\Laravel\Page;

use SequentSoft\ThreadFlow\Contracts\Forms\FormInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\CommonIncomingMessageInterface;
use Illuminate\Support\Facades\App;
use SequentSoft\ThreadFlow\Page\AbstractPage;
use SequentSoft\ThreadFlow\Page\BaseFormPage;
use Illuminate\Contracts\View\View as ViewContract;

class Page extends AbstractPage
{
    protected function callHandlerMethod(string $method, ?CommonIncomingMessageInterface $message): mixed
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
            $result = $this->htmlMessage($result->render());
        }

        return parent::handleResult($result);
    }
}
