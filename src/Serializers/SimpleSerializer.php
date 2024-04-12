<?php

namespace SequentSoft\ThreadFlow\Serializers;

use SequentSoft\ThreadFlow\Contracts\Serializers\SerializerInterface;

class SimpleSerializer implements SerializerInterface
{
    public function serialize(mixed $data): string
    {
        return serialize($data);
    }

    public function unserialize(?string $serializedData): mixed
    {
        if ($serializedData === null) {
            return null;
        }

        return unserialize($serializedData);
    }
}
