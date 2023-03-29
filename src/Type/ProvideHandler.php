<?php

/*
 * @author       Kamil Smolak <kamil@smol.pl>
 * @link         http://www.smol.pl
 * @copyright    Copyright (c) 2023 Kamil Smolak
 */

namespace SmolCms\Bundle\ContentBlock\Type;

use SmolCms\Bundle\ContentBlock\Form\Type\BlockWrapperType;
use SmolCms\Bundle\ContentBlock\Metadata\ContentBlockRegistry;
use SmolCms\Bundle\ContentBlock\ResolvedProperty;
use Symfony\Component\Form\FormBuilderInterface;
use Traversable;

class ProvideHandler extends AbstractHandler
{
    public function __construct(private readonly ContentBlockRegistry $registry)
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

        $builder->add($property->getPropertyName(), BlockWrapperType::class, [
            'label' => null,
            'property' => $property,
            'allowed' => $allowed,
            'allow_change' => $type->allowChange,
        ]);
    }

    private function resolveName(ResolvedProperty $property): string
    {
        $blockMetadata = $property->getInnerBlockMetadata();

        return $blockMetadata->name ?? $property->getMetadata()->getPropertyType();
    }

    private function resolveAllowed(ProvideType $type, ResolvedProperty $property): iterable
    {
        $allowed = $type->allowed;
        if (!$allowed) {
            $providers = $this->registry->providersFor($this->resolveName($property));
            $allowed = $providers instanceof Traversable ? iterator_to_array($providers) : $providers;
        }

        return $allowed;
    }
}
