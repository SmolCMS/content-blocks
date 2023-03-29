<?php

/*
 * @author       Kamil Smolak <kamil@smol.pl>
 * @link         http://www.smol.pl
 * @copyright    Copyright (c) 2023 Kamil Smolak
 */

namespace SmolCms\Bundle\ContentBlock;

use SmolCms\Bundle\ContentBlock\ContentBlock\Builtin;
use SmolCms\Bundle\ContentBlock\ContentBlock\Group;
use SmolCms\Bundle\ContentBlock\Metadata\BlockMetadata;
use SmolCms\Bundle\ContentBlock\Metadata\ContentBlockRegistry;
use SmolCms\Bundle\ContentBlock\Metadata\PropertyMetadata;
use Symfony\Component\Validator\Mapping\ClassMetadataInterface;
use Symfony\Component\Validator\Mapping\MetadataInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ResolvedPropertyFactory
{
    public function __construct(
        private readonly ContentBlockRegistry $registry,
        private readonly ValidatorInterface $validator,
    ) {
    }

    public function create(BlockMetadata $parentBlock, PropertyMetadata $property): ResolvedProperty
    {
        $innerBlockMetadata = $this->resolveInnerBlockMetadata($property);
        $validatorClassMetadata = $this->validator->getMetadataFor($parentBlock->class);
        $constraints = $this->getConstraints($validatorClassMetadata, $property);

        return new ResolvedProperty(
            $parentBlock,
            $property,
            $innerBlockMetadata,
            $constraints
        );
    }

    private function getConstraints(MetadataInterface $validatorClassMetadata, PropertyMetadata $property): array
    {
        if (!$validatorClassMetadata instanceof ClassMetadataInterface) {
            return [];
        }

        $constraints = [];
        $validatorPropertyMetadata = $validatorClassMetadata->getPropertyMetadata($property->getPropertyName());
        foreach ($validatorPropertyMetadata as $metadata) {
            foreach ($metadata->getConstraints() as $constraint) {
                $constraints[] = $constraint;
            }
        }

        return $constraints;
    }

    private function resolveInnerBlockMetadata(PropertyMetadata $property): ?BlockMetadata
    {
        $propertyType = $property->getPropertyType();

        if ($property->isBuiltin()) {
            if ($propertyType === 'array') {
                return $this->registry->metadataFor(Group::class);
            }

            if (Builtin::supports($propertyType)) {
                return $this->registry->metadataFor(Builtin::class);
            }
        }

        if (!$propertyType) {
            throw new \LogicException(
                sprintf(
                    'Could not guess block type for property "%s" in class "%s".',
                    $property->getPropertyName(),
                    $property->getClass()
                )
            );
        }

        if ($this->registry->has($propertyType)) {
            return $this->registry->metadataFor($propertyType);
        }

        return null;
    }
}
