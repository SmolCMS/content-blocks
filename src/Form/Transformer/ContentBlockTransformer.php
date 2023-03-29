<?php

/*
 * @author       Kamil Smolak <kamil@smol.pl>
 * @link         http://www.smol.pl
 * @copyright    Copyright (c) 2023 Kamil Smolak
 */

namespace SmolCms\Bundle\ContentBlock\Form\Transformer;

use SmolCms\Bundle\ContentBlock\Metadata\BlockMetadata;
use SmolCms\Bundle\ContentBlock\Metadata\ContentBlockRegistry;
use Symfony\Component\Form\DataTransformerInterface;

class ContentBlockTransformer implements DataTransformerInterface
{
    public function __construct(
        private readonly ContentBlockRegistry $registry,
        private readonly ?BlockMetadata $singleChoice,
    ) {
    }

    public function transform(mixed $value): ?array
    {
        if (!$value) {
            return $value;
        }

        $metadata = $this->singleChoice ?? $this->registry->metadataFor($value['name']);

        return [
            'name' => $metadata,
            'properties' => $value['properties'],
        ];
    }

    public function reverseTransform(mixed $value): ?array
    {
        if ($this->singleChoice) {
            $metadata = $this->singleChoice;
        } else {
            if (!isset($value['name'])) {
                return null;
            }

            $metadata = $value['name'];
            if (!$metadata instanceof BlockMetadata) {
                return null;
            }
        }

        return [
            'name' => $metadata->name,
            'properties' => $value['properties'],
        ];
    }
}
