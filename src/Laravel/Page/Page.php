<?php

namespace SequentSoft\ThreadFlow\Laravel\Page;

use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\CommonIncomingMessageInterface;
use Illuminate\Support\Facades\App;

class Page extends \SequentSoft\ThreadFlow\Page\AbstractPage
{
    protected function callHandlerMethod(string $method, ?CommonIncomingMessageInterface $message): mixed
    {
        return App::call([$this, $method], [
            'message' => $message,
        ]);
    }
}
