<?php

namespace SmolCms\Bundle\ContentBlock\Serializer;

use SmolCms\Bundle\ContentBlock\Mapper\FromBuiltinMapper;
use SmolCms\Bundle\ContentBlock\Type\Builtin;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

readonly class BuiltinDenormalizer implements DenormalizerInterface
{
    public function denormalize(mixed $data, string $type, string $format = null, array $context = []): mixed
    {
        return FromBuiltinMapper::cast($type, $data);
    }

    public function supportsDenormalization(mixed $data, string $type, string $format = null, array $context = []): bool
    {
        if (is_a($type, Builtin::class, true)) {
            return true;
        }

        if (!isset($context[ContentBlockContextBuilder::OUTER_PROPERTY])) {
            return false;
        }

        $innerType = $context[ContentBlockContextBuilder::OUTER_PROPERTY]->getInnerBlockMetadata()?->class;

        return $innerType && is_a($innerType, Builtin::class, true) && Builtin::supports($type);
    }
}
