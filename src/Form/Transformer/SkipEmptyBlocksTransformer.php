<?php

namespace SmolCms\Bundle\ContentBlock\Form\Transformer;

use Symfony\Component\Form\DataTransformerInterface;

class SkipEmptyBlocksTransformer implements DataTransformerInterface
{
    public function transform(mixed $value): mixed
    {
        return $value;
    }

    public function reverseTransform(mixed $value): ?array
    {
        if ($value === null) {
            return null;
        }

        return array_filter($value, static fn ($v) => $v !== null);
    }
}
