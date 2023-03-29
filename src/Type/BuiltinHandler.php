<?php

/*
 * @author       Kamil Smolak <kamil@smol.pl>
 * @link         http://www.smol.pl
 * @copyright    Copyright (c) 2023 Kamil Smolak
 */

namespace SmolCms\Bundle\ContentBlock\Type;

use SmolCms\Bundle\ContentBlock\Form\Transformer\BlockValueWrappedTransformer;
use SmolCms\Bundle\ContentBlock\ResolvedBlock;
use SmolCms\Bundle\ContentBlock\ResolvedProperty;
use SmolCms\Bundle\ContentBlock\Type\Exception\UnexpectedTypeException;
use SmolCms\Bundle\ContentBlock\Validator\ContentBlock;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class BuiltinHandler extends AbstractHandler
{
    /**
     * @throws UnexpectedTypeException
     */
    public function buildFormForBlock(ResolvedBlock $block, FormBuilderInterface $builder): void
    {
        $type = $block->getType();
        if (!$type instanceof GenericType) {
            throw new Exception\UnexpectedTypeException($type, GenericType::class);
        }

        $formOptions = $block->getFormOptions();

        $builder->add('value', TextType::class, $formOptions);
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

        $formOptions = $property->getFormOptions();
        $formOptions['constraints'] = new ContentBlock($formOptions['constraints']);

        $builder->add($property->getPropertyName(), TextType::class, $formOptions);
        $builder->get($property->getPropertyName())
            ->addModelTransformer(new BlockValueWrappedTransformer($property->getInnerBlockMetadata()));
    }
}
