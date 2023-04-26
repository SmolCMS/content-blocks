<?php

/*
 * @author       Kamil Smolak <kamil@smol.pl>
 * @link         http://www.smol.pl
 * @copyright    Copyright (c) 2023 Kamil Smolak
 */

namespace SmolCms\Bundle\ContentBlock;

use SmolCms\Bundle\ContentBlock\Metadata\BlockMetadata;
use SmolCms\Bundle\ContentBlock\Metadata\MetadataRegistry;
use SmolCms\Bundle\ContentBlock\Metadata\PropertyMetadata;
use SmolCms\Bundle\ContentBlock\Type\Builtin;
use SmolCms\Bundle\ContentBlock\Type\Group;
use SmolCms\Bundle\ContentBlock\Type\GroupType;
use Symfony\Component\Validator\Mapping\ClassMetadataInterface;
use Symfony\Component\Validator\Mapping\MetadataInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

readonly class ResolvedPropertyFactory
{
    public function __construct(
        private MetadataRegistry $registry,
        private ValidatorInterface $validator,
    ) {
    }

    public function create(BlockMetadata $parentBlock, PropertyMetadata $property): ResolvedProperty
    {
        $innerBlockMetadata = $this->resolveInnerBlockMetadata($property);

        //todo: nie wiem czy potrzebne? To tylko po to żeby runtime sprawdzić czy to pole jest do obsłużenia
        if ($property->getPropertyType() === 'mixed' && $property->getProperty()->type === null) {
            throw new \LogicException(sprintf(
                'Cannot guess type for mixed property "%s::$%s". You should configure type explicit via `#[Property(type: ...)]` attribute.',
                $property->getClass(), $property->getPropertyName()
            ));
        }

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
            if (Group::supports($propertyType) && is_a($property->getProperty()->type, GroupType::class, true)) {
                return $this->registry->metadataFor(Group::class);
            }

            if (Builtin::supports($propertyType)) {
                return $this->registry->metadataFor(Builtin::class);
            }
        }

        if (!$propertyType) {
            throw new \LogicException(sprintf(
                'Could not guess block type for property "%s" in class "%s".',
                $property->getPropertyName(),
                $property->getClass()
            ));
        }

        if ($this->registry->has($propertyType)) {
            return $this->registry->metadataFor($propertyType);
        }

        return null;
    }
}
