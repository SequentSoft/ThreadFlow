<?php

namespace SequentSoft\ThreadFlow\Traits;

use ReflectionIntersectionType;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionUnionType;

trait AcceptableArguments
{
    protected function isArgumentAcceptableTo(object $object, string $method, mixed $argument): bool
    {
        $reflection = new ReflectionMethod($object, $method);

        $params = $reflection->getParameters();

        if (count($params) < 1) {
            return false;
        }

        $type = $params[0]->getType();

        // no type
        if ($type === null) {
            return true;
        }

        // union types
        if ($type instanceof ReflectionUnionType) {
            foreach ($type->getTypes() as $type) {
                if ($type instanceof ReflectionNamedType && $argument instanceof ($type->getName())) {
                    return true;
                }
            }

            return false;
        }

        // intersection types
        if ($type instanceof ReflectionIntersectionType) {
            foreach ($type->getTypes() as $type) {
                if (! $type instanceof ReflectionNamedType || ! $argument instanceof ($type->getName())) {
                    return false;
                }
            }

            return true;
        }

        // named types
        if ($type instanceof ReflectionNamedType) {
            return $argument instanceof ($type->getName());
        }

        return false;
    }
}
