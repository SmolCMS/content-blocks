<?php

/*
 * @author       Kamil Smolak <kamil@smol.pl>
 * @link         http://www.smol.pl
 * @copyright    Copyright (c) 2023 Kamil Smolak
 */

namespace SmolCms\Bundle\ContentBlock\Form\Transformer;

use SmolCms\Bundle\ContentBlock\Metadata\BlockMetadata;
use Symfony\Component\Form\DataTransformerInterface;

class BlockValueWrappedTransformer implements DataTransformerInterface
{
    public function __construct(
        private readonly BlockMetadata $metadata,
    ) {
    }

    public function transform(mixed $value): mixed
    {
        return $value['properties']['value'] ?? $value;
    }

    public function reverseTransform(mixed $value): array
    {
        return [
            'name' => $this->metadata->name,
            'properties' => [
                'value' => $value,
            ],
        ];
    }
}
