<?php

/*
 * @author       Kamil Smolak <kamil@smol.pl>
 * @link         http://www.smol.pl
 * @copyright    Copyright (c) 2023 Kamil Smolak
 */

namespace SmolCms\Bundle\ContentBlock\Type;

use SmolCms\Bundle\ContentBlock\Form\Transformer\GroupBlockTransformer;
use SmolCms\Bundle\ContentBlock\Form\Transformer\SkipEmptyBlocksTransformer;
use SmolCms\Bundle\ContentBlock\Form\Type\ContentBlockWrapperType;
use SmolCms\Bundle\ContentBlock\Mapper\InvalidMappingStrategy;
use SmolCms\Bundle\ContentBlock\Metadata\BlockMetadata;
use SmolCms\Bundle\ContentBlock\Metadata\MetadataRegistry;
use SmolCms\Bundle\ContentBlock\ResolvedBlock;
use SmolCms\Bundle\ContentBlock\ResolvedProperty;
use SmolCms\Bundle\ContentBlock\Type\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Event\PreSubmitEvent;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;

readonly class GroupTypeHandler implements BlockTypeHandlerInterface, PropertyTypeHandlerInterface
{
    public function __construct(
        private MetadataRegistry $registry,
    ) {
    }

    public function initializeObject(BlockMetadata $blockMetadata): ?object
    {
        return new ($blockMetadata->class)();
    }

    /**
     * @throws UnexpectedTypeException
     */
    public function buildFormForBlock(ResolvedBlock $block, FormBuilderInterface $builder): void
    {
        $type = $block->getType();

        $this->addField($builder, 'items', $type, $block->getMetadata()->class);
    }

    /**
     * @throws UnexpectedTypeException
     */
    public function buildFormForProperty(ResolvedProperty $property, FormBuilderInterface $builder): void
    {
        $propertyName = $property->getPropertyName();
        $propertyType = $property->getMetadata()->getPropertyType();
        $type = $property->getType();

        $this->addField($builder, $propertyName, $type, $propertyType);
    }

    public function resolveAllowed(GroupType $type): array
    {
        $allowedBlocks = $type->getAllowedBlocks();
        if (!$type->provide) {
            return $allowedBlocks ?? iterator_to_array($this->registry->all());
        }

        $allowed = [];

        if ($allowedBlocks) {
            foreach ($allowedBlocks as $item) {
                $providers = $this->registry->providersFor($item);
                foreach ($providers as $provider) {
                    $allowed[] = $provider;
                }
            }
        }

        return $allowed;
    }

    /**
     * @throws UnexpectedTypeException
     */
    public function addField(
        FormBuilderInterface $builder,
        string $propertyName,
        TypeInterface $type,
        string $propertyType,
    ): void {
        if (!$type instanceof GroupType) {
            throw new Exception\UnexpectedTypeException($type, GroupType::class);
        }

        $builder->add($propertyName, $type->formType, $type->formOptions + [
            'label' => $type->label ?? null,
            'entry_type' => ContentBlockWrapperType::class,
            'entry_options' => [
                'allowed' => $this->resolveAllowed($type),
                'invalid_mapping_strategy' => InvalidMappingStrategy::IGNORE,
                'block_selector_options' => $type->blockSelectorOptions,
            ],
            'allow_add' => true,
            'allow_delete' => true,
            'error_bubbling' => false,
        ]);

        $builder->get($propertyName)->addEventListener(FormEvents::PRE_SUBMIT, function (PreSubmitEvent $event) {
            //This is needed for correct ordering blocks
            $form = $event->getForm();
            $form->setData(null);

            foreach ($form as $item) {
                $form->remove($item->getName());
            }
        }, 100);

        $builder->get($propertyName)
            ->addModelTransformer(new SkipEmptyBlocksTransformer());

        if ($propertyType === Group::class) {
            $builder->get($propertyName)
                ->addModelTransformer(new GroupBlockTransformer());
        }
    }
}
