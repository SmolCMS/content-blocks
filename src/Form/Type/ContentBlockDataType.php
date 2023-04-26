<?php

/*
 * @author       Kamil Smolak <kamil@smol.pl>
 * @link         http://www.smol.pl
 * @copyright    Copyright (c) 2022 Kamil Smolak
 */

namespace SmolCms\Bundle\ContentBlock\Form\Type;

use SmolCms\Bundle\ContentBlock\Form\Transformer\MapperTransformer;
use SmolCms\Bundle\ContentBlock\Mapper\InvalidMappingStrategy;
use SmolCms\Bundle\ContentBlock\Mapper\MapperInterface;
use SmolCms\Bundle\ContentBlock\Metadata\BlockMetadata;
use SmolCms\Bundle\ContentBlock\Proxy\ContentBlockProxyInterface;
use SmolCms\Bundle\ContentBlock\ResolvedBlockFactory;
use SmolCms\Bundle\ContentBlock\ResolvedProperty;
use SmolCms\Bundle\ContentBlock\Type\BlockTypeHandlerInterface;
use SmolCms\Bundle\ContentBlock\Type\Factory\TypeHandlerFactory;
use SmolCms\Bundle\ContentBlock\Type\TypeInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContentBlockDataType extends AbstractType
{
    public function __construct(
        private readonly TypeHandlerFactory $handlerFactory,
        private readonly ResolvedBlockFactory $resolver,
        private readonly MapperInterface $mapper,
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => false,
            'empty_data' => function (FormInterface $form) {
                $options = $form->getConfig()->getOptions();
                $blockMetadata = $options['block_metadata'];
                $type = $options['type'];
                $object = $this->getTypeHandler($type)->initializeObject($blockMetadata);

                if ($object instanceof ContentBlockProxyInterface) {
                    $object->__setMetadata($blockMetadata);
                }

                return $object;
            },
        ]);

        $resolver->setNormalizer('data_class', function (Options $options, ?string $dataClass) {
            return $dataClass ?? $options['block_metadata']->class;
        });

        $resolver->setDefault('property', null);
        $resolver->setAllowedTypes('property', [ResolvedProperty::class, 'null']);

        $resolver->setRequired('block_metadata');
        $resolver->setAllowedTypes('block_metadata', [BlockMetadata::class]);

        $resolver->setDefault('type', null);
        $resolver->setAllowedTypes('type', [TypeInterface::class, 'null']);
        $resolver->setNormalizer('type', function (Options $options) {
            return $options['block_metadata']->type;
        });

        $resolver->define('invalid_mapping_strategy')
            ->default(InvalidMappingStrategy::THROW)
            ->allowedTypes(InvalidMappingStrategy::class);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $property = $options['property'];
        $blockMetadata = $options['block_metadata'];
        assert($blockMetadata instanceof BlockMetadata);

        $block = $this->resolver->create($property, $blockMetadata);
        $type = $options['type'];

        $this->getTypeHandler($type)->buildFormForBlock($block, $builder);

        $builder->addViewTransformer(new MapperTransformer($this->mapper, $blockMetadata->class, $options['invalid_mapping_strategy']));
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        $blockMetadata = $options['block_metadata'];
        assert($blockMetadata instanceof BlockMetadata);

        $view->vars['block_prefixes'][] = 'smol_block';
        $view->vars['block_prefixes'][] = 'smol_block_' . $blockMetadata->name;

        $property = $options['property'];
        if ($property instanceof ResolvedProperty) {
            $view->vars['block_prefixes'][] = 'smol_block_property_' . $property->getParentBlockMetadata()->name . '_' . $property->getPropertyName();
        }

        $view->vars['block_metadata'] = $options['block_metadata'];

        if (\count($form) === 0) {
            $view->vars['value'] = null;
        }
    }

    private function getTypeHandler(TypeInterface $type): BlockTypeHandlerInterface
    {
        return $this->handlerFactory->createForBlock($type->getHandler());
    }
}
