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
        $rules = $field->getValidationRules();

        if (! $rules) {
            return null;
        }

        $key = $field->getKey();

        $validationAttributes = $field->getValidationAttributes() ?: [$key => '"' . $field->getCaption() . '"'];

        $validator = $this->validationFactory()->make(
            [$key => $this->prepareValueForValidation($field, $message)],
            [$key => $rules],
            $field->getValidationMessages(),
            $validationAttributes,
        );

        if (! $validator->fails()) {
            return null;
        }

        return $validator->errors()->first($key);
    }
}
