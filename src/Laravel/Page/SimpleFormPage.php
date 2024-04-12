<?php

namespace SequentSoft\ThreadFlow\Laravel\Page;

use SequentSoft\ThreadFlow\Contracts\Forms\FormFieldInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular\IncomingMessageInterface;
use SequentSoft\ThreadFlow\Page\BaseFormPage;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;

class SimpleFormPage extends BaseFormPage
{
    protected function validationFactory(): ValidationFactory
    {
        return app(ValidationFactory::class);
    }

    protected function validate(FormFieldInterface $field, IncomingMessageInterface $message): ?string
    {
        $rules = $field->getRules();

        if (! $rules) {
            return null;
        }

        $key = $field->getKey();

        $validator = $this->validationFactory()->make(
            [$key => $this->prepareValueForValidation($field, $message)],
            [$key => $rules],
            $field->getMessages()
        );

        if (! $validator->fails()) {
            return null;
        }

        return $validator->errors()->first($key);
    }
}
