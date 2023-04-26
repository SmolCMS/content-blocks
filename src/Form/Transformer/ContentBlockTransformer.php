<?php

/*
 * @author       Kamil Smolak <kamil@smol.pl>
 * @link         http://www.smol.pl
 * @copyright    Copyright (c) 2023 Kamil Smolak
 */

namespace SmolCms\Bundle\ContentBlock\Form\Transformer;

use SmolCms\Bundle\ContentBlock\Mapper\MapperException;
use SmolCms\Bundle\ContentBlock\Mapper\MapperInterface;
use SmolCms\Bundle\ContentBlock\Metadata\BlockMetadata;
use SmolCms\Bundle\ContentBlock\Metadata\MetadataRegistry;
use SmolCms\Bundle\ContentBlock\ResolvedProperty;
use Symfony\Component\Form\DataTransformerInterface;

readonly class ContentBlockTransformer implements DataTransformerInterface
{
    public function __construct(
        private ?ResolvedProperty $property,
        private MetadataRegistry $registry,
        private ?BlockMetadata $singleChoice,
        private MapperInterface $mapper,
    ) {
    }

    /**
     * @throws MapperException
     */
    public function transform(mixed $value): ?array
    {
        if (!$value) {
            return null;
        }

        if (is_array($value)) {
            return $value;
        }

        $metadata = $this->singleChoice ?? $this->registry->metadataFor($value);

        if (!is_a($value, $metadata->class)) {
            $value = $this->mapper->map($value, $metadata->class);
        }

        return [
            'name' => $metadata,
            'data' => $value,
        ];
    }

    /**
     * @throws MapperException
     */
    public function reverseTransform(mixed $value): mixed
    {
        if (!$this->singleChoice) {
            if (!isset($value['name'])) {
                return null;
            }

            $metadata = $value['name'];
            if (!$metadata instanceof BlockMetadata) {
                return null;
            }
        }

        $object = $value['data'] ?? null;
        if (!$object) {
            return $object;
        }

        if (!$this->property) {
            return $object;
        }

        $propertyType = $this->property->getMetadata()->getPropertyType();
        if (is_a($object, $propertyType, true) || $propertyType === 'mixed') {
            return $object;
        }

        return $this->mapper->map($object, $propertyType);
    }
}
