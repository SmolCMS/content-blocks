<?php

/*
 * @author       Kamil Smolak <kamil@smol.pl>
 * @link         http://www.smol.pl
 * @copyright    Copyright (c) 2022 Kamil Smolak
 */

namespace SmolCms\Bundle\ContentBlock\Form\Type;

use SmolCms\Bundle\ContentBlock\Metadata\BlockMetadata;
use SmolCms\Bundle\ContentBlock\ResolvedBlockFactory;
use SmolCms\Bundle\ContentBlock\ResolvedProperty;
use SmolCms\Bundle\ContentBlock\Type\Factory\ContentBlockHandlerFactory;
use SmolCms\Bundle\ContentBlock\Type\TypeInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BlockType extends AbstractType
{
    public function __construct(
        private readonly ContentBlockHandlerFactory $handlerFactory,
        private readonly ResolvedBlockFactory $resolver,
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => false,
        ]);

        $resolver->setDefault('property', null);
        $resolver->setAllowedTypes('property', [ResolvedProperty::class, 'null']);

        $resolver->setRequired('block_metadata');
        $resolver->setAllowedTypes('block_metadata', [BlockMetadata::class]);

        $resolver->setRequired('type');
        $resolver->setAllowedTypes('type', [TypeInterface::class]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $property = $options['property'];
        $blockMetadata = $options['block_metadata'];
        assert($blockMetadata instanceof BlockMetadata);

        $block = $this->resolver->create($property, $blockMetadata);

        $type = $options['type'];
        assert($type instanceof TypeInterface);

        $handler = $this->handlerFactory->create($type->getHandler());
        $handler->buildFormForBlock($block, $builder);
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        $blockMetadata = $options['block_metadata'];
        assert($blockMetadata instanceof BlockMetadata);

        $view->vars['block_prefixes'][] = 'smol_block_' . $blockMetadata->name;

        $property = $options['property'];
        if ($property instanceof ResolvedProperty) {
            $view->vars['block_prefixes'][] = 'smol_block_property_' . $property->getParentBlockMetadata()->name . '_' . $property->getPropertyName();
        }

        $view->vars['block_metadata'] = $options['block_metadata'];
        $view->vars['preview_block_name'] = 'smol_block_' . $blockMetadata->name . '_preview';
    }
}
