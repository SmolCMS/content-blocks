<?php

/*
 * @author       Kamil Smolak <kamil@smol.pl>
 * @link         http://www.smol.pl
 * @copyright    Copyright (c) 2023 Kamil Smolak
 */

namespace SmolCms\Bundle\ContentBlock\Type;

use SmolCms\Bundle\ContentBlock\Form\Transformer\BlockValueWrappedTransformer;
use SmolCms\Bundle\ContentBlock\Form\Transformer\BlockWrappedTransformer;
use SmolCms\Bundle\ContentBlock\Form\Type\BlockType;
use SmolCms\Bundle\ContentBlock\ResolvedBlock;
use SmolCms\Bundle\ContentBlock\ResolvedProperty;
use SmolCms\Bundle\ContentBlock\Type\Exception\UnexpectedTypeException;
use SmolCms\Bundle\ContentBlock\Type\Factory\ContentBlockHandlerFactory;
use Symfony\Component\Form\FormBuilderInterface;

class GenericHandler extends AbstractHandler
{
    public function __construct(
        private readonly ContentBlockHandlerFactory $handlerFactory,
    ) {
    }

    /**
     * @throws UnexpectedTypeException
     */
    public function buildFormForBlock(ResolvedBlock $block, FormBuilderInterface $builder): void
    {
        foreach ($block->getProperties() as $resolvedProperty) {
            $this->buildFormForProperty($resolvedProperty, $builder);
        }
    }

    /**
     * @throws UnexpectedTypeException
     */
    public function buildFormForProperty(ResolvedProperty $property, FormBuilderInterface $builder): void
    {
        $type = $property->getType();
        if (!$type instanceof GenericType) {
            throw new Exception\UnexpectedTypeException($type, GenericType::class);
        }

        $name = $property->getPropertyName();

        if ($type->formType) {
            $formOptions = $property->getFormOptions();

            $builder->add($name, $type->formType, $formOptions);
            $builder->get($name)->addModelTransformer(new BlockValueWrappedTransformer($property->getInnerBlockMetadata()));

            return;
        }

        if ($type->usePropertyForm) {
            $handler = $this->handlerFactory->create($property->getType()->getHandler());
            $handler->buildFormForProperty($property, $builder);

            return;
        }

        $builder->add($name, BlockType::class, [
            'property' => $property,
            'block_metadata' => $property->getInnerBlockMetadata(),
            'type' => $type,
        ]);
        $builder->get($name)->addModelTransformer(new BlockWrappedTransformer($property->getInnerBlockMetadata()));
    }
}
