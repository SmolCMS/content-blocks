<?php

/*
 * @author       Kamil Smolak <kamil@smol.pl>
 * @link         http://www.smol.pl
 * @copyright    Copyright (c) 2023 Kamil Smolak
 */

namespace SmolCms\Bundle\ContentBlock\Type;

use SmolCms\Bundle\ContentBlock\Form\Type\ContentBlockDataType;
use SmolCms\Bundle\ContentBlock\Metadata\BlockMetadata;
use SmolCms\Bundle\ContentBlock\ResolvedBlock;
use SmolCms\Bundle\ContentBlock\ResolvedProperty;
use SmolCms\Bundle\ContentBlock\Type\Exception\UnexpectedTypeException;
use SmolCms\Bundle\ContentBlock\Type\Factory\TypeHandlerFactory;
use Symfony\Component\Form\FormBuilderInterface;

readonly class GenericTypeHandler implements BlockTypeHandlerInterface, PropertyTypeHandlerInterface
{
    public function __construct(
        private TypeHandlerFactory $handlerFactory,
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

        if ($type->usePropertyForm) {
            $handler = $this->handlerFactory->createForProperty($type->getHandler());
            $handler->buildFormForProperty($property, $builder);

            return;
        }

        $blockMetadata = $property->getInnerBlockMetadata();
        if (!$blockMetadata) {
            throw new \LogicException(sprintf(
                'Cannot handle property "%s::$%s" in "%s", because inner block is empty. You should use other handler.',
                $property->getClass(), $property->getPropertyName(), static::class,
            ));
        }

        $builder->add($property->getPropertyName(), ContentBlockDataType::class, [
            'property' => $property,
            'block_metadata' => $blockMetadata,
            'type' => $type,
        ]);
    }
}
