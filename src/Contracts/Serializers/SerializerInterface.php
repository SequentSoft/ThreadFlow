<?php

namespace SequentSoft\ThreadFlow\Contracts\Serializers;

interface SerializerInterface
{
    public function serialize(mixed $data): string;

    public function unserialize(string $serializedData): mixed;
}
