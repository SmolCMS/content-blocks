<?php

/*
 * @author       Kamil Smolak <kamil@smol.pl>
 * @link         http://www.smol.pl
 * @copyright    Copyright (c) 2023 Kamil Smolak
 */

namespace SmolCms\Bundle\ContentBlock\Metadata;

use SmolCms\Bundle\ContentBlock\Attribute\AsContentBlock;
use SmolCms\Bundle\ContentBlock\Attribute\Property;

readonly class BlockMetadataReader
{
    private \ReflectionClass $reflClass;

    public function __construct(private string $class)
    {
        $this->reflClass = new \ReflectionClass($this->class);
    }

    /**
     * @throws MetadataReaderException
     */
    public function getMetadata(): BlockMetadata
    {
        $reflectionAttribute = $this->reflClass->getAttributes(AsContentBlock::class)[0] ?? null;
        if (!$reflectionAttribute) {
            throw new MetadataReaderException(sprintf('Could not get block metadata from class "%s".', $this->class));
        }

        $attribute = $reflectionAttribute->newInstance();
        assert($attribute instanceof AsContentBlock);

        return new BlockMetadata(
            $attribute->name,
            $attribute->label,
            $this->class,
            $attribute->type,
            $attribute->provides,
        );
    }

    /**
     * @return list<string, PropertyMetadata>
     */
    public function getProperties(): iterable
    {
        foreach ($this->reflClass->getProperties() as $reflectionProperty) {
            foreach ($reflectionProperty->getAttributes(Property::class) as $reflectionAttribute) {
                $property = $reflectionAttribute->newInstance();
                assert($property instanceof Property);

                $propertyType = $reflectionProperty->getType();

                $metadata = new PropertyMetadata(
                    $this->class,
                    $reflectionProperty->getName(),
                    $property,
                    $propertyType instanceof \ReflectionNamedType ? $propertyType->getName() : 'mixed',
                    $propertyType?->allowsNull() ?? false,
                    $propertyType instanceof \ReflectionNamedType && $propertyType->isBuiltin(),
                );

                yield $reflectionProperty->getName() => $metadata;
            }
        }
    }
}
