<?php

/*
 * @author       Kamil Smolak <kamil@smol.pl>
 * @link         http://www.smol.pl
 * @copyright    Copyright (c) 2023 Kamil Smolak
 */

namespace SmolCms\Bundle\ContentBlock\Serializer;

use SmolCms\Bundle\ContentBlock\ContentBlock;
use SmolCms\Bundle\ContentBlock\ContentBlockFactory;
use SmolCms\Bundle\ContentBlock\Mapper\MapperInterface;
use SmolCms\Bundle\ContentBlock\ResolvedBlockFactory;
use SmolCms\Bundle\ContentBlock\ResolvedProperty;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class ContentBlockDenormalizer implements DenormalizerInterface
{
    private PropertyAccessor $propertyAccessor;

    public function __construct(
        private readonly ContentBlockFactory $contentBlockFactory,
        private readonly ResolvedBlockFactory $resolvedBlockFactory,
        private readonly MapperInterface $mapper,
    ) {
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
    }

    public function denormalize(mixed $data, string $type, string $format = null, array $context = []): mixed
    {
        $block = $data;
        assert($block instanceof ContentBlock);

        $blockMetadata = $block->getMetadata();
        if ($type === ContentBlock\Builtin::class) {
            $item = new ContentBlock\Builtin();
            $item->value = $data->getProperties()['value'] ?? null;

            return $item;
        }

        $resolved = $this->resolvedBlockFactory->create(null, $blockMetadata);
        $className = $blockMetadata->class;
        $object = new $className();

        foreach ($resolved->getProperties() as $property) {
            $propertyPath = $property->getPropertyName();
            $value = $this->resolveValue($block, $property);

            $this->propertyAccessor->setValue($object, $propertyPath, $value);
        }

        if (is_a($object, $type)) {
            return $object;
        }

        return $this->mapper->map($object, $type);
    }

    public function supportsDenormalization(mixed $data, string $type, string $format = null): bool
    {
        return $data instanceof ContentBlock;
    }

    public function resolveValue(ContentBlock $block, ResolvedProperty $property): mixed
    {
        $propertyPath = $property->getPropertyName();
        $value = $block->getProperties()[$propertyPath] ?? null;
        if ($value === null) {
            return null;
        }

        if ($propertyPath === 'items' && $block->getMetadata()->class === ContentBlock\Group::class) {
            $items = [];

            foreach ($value as $item) {
                $innerBlock = $this->contentBlockFactory->create($item);
                $object = $this->denormalize($innerBlock, $innerBlock->getMetadata()->class);
                $items[] = $object;
            }

            return $items;
        }

        if (!isset($value['name'])) {
            return $value;
        }

        if ($property->getMetadata()->isBuiltin()) {
            $innerBlock = $this->contentBlockFactory->create($value);
            $object = $this->denormalize($innerBlock, $innerBlock->getMetadata()->class);
            return $this->mapper->map($object, $property->getMetadata()->getPropertyType());
        }

        $innerBlockMetadata = $property->getInnerBlockMetadata();
        if ($innerBlockMetadata) {
            $innerBlock = $this->contentBlockFactory->create($value);
            return $this->denormalize($innerBlock, $innerBlockMetadata->class);
        }

        if ($property->getMetadata()->getPropertyType() !== get_debug_type($value)) {
            $innerBlock = $this->contentBlockFactory->create($value);
            return $this->denormalize($innerBlock, $property->getMetadata()->getPropertyType());
        }

        return $value;
    }
}
