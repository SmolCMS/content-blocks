<?php

/*
 * @author       Kamil Smolak <kamil@smol.pl>
 * @link         http://www.smol.pl
 * @copyright    Copyright (c) 2023 Kamil Smolak
 */

namespace SmolCms\Bundle\ContentBlock\Type;

use SmolCms\Bundle\ContentBlock\Form\Type\ContentBlockWrapperType;
use SmolCms\Bundle\ContentBlock\Metadata\MetadataRegistry;
use SmolCms\Bundle\ContentBlock\ResolvedProperty;
use Symfony\Component\Form\FormBuilderInterface;
use Traversable;

readonly class ProvideTypeHandler implements PropertyTypeHandlerInterface
{
    public function __construct(private MetadataRegistry $registry)
    {
    }

    /**
     * @throws Exception\UnexpectedTypeException
     */
    public function buildFormForProperty(ResolvedProperty $property, FormBuilderInterface $builder): void
    {
        $type = $property->getType();
        if (!$type instanceof ProvideType) {
            throw new Exception\UnexpectedTypeException($type, ProvideType::class);
        }

        $allowed = $this->resolveAllowed($type, $property);
        if (!$allowed) {
            throw new \LogicException('No configured `allowed` option.');
        }

        if ($type->allowChange && !$property->getMetadata()->isNullable()) {
            throw new \LogicException(sprintf(
                'Property "%s::$%s" should be nullable.',
                $property->getMetadata()->getClass(),
                $property->getMetadata()->getPropertyName(),
            ));
        }

        $builder->add($property->getPropertyName(), ContentBlockWrapperType::class, [
            'label' => $type->label ?? null,
            'property' => $property,
            'allowed' => $allowed,
            'allow_change' => $type->allowChange,
            'invalid_mapping_strategy' => $property->getMetadata()->getProperty()->invalidMappingStrategy,
            'block_selector_options' => $type->blockSelectorOptions,
        ]);
    }

    private function resolveAllowed(ProvideType $type, ResolvedProperty $property): iterable
    {
        if ($type->allowed) {
            return $type->allowed;
        }

        $providers = $this->registry->providersFor($this->resolveName($property));

        return $providers instanceof Traversable ? iterator_to_array($providers) : $providers;
    }

    private function resolveName(ResolvedProperty $property): string
    {
        $blockMetadata = $property->getInnerBlockMetadata();

        return $blockMetadata->name ?? $property->getMetadata()->getPropertyType();
    }
}
