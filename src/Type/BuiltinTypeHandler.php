<?php

/*
 * @author       Kamil Smolak <kamil@smol.pl>
 * @link         http://www.smol.pl
 * @copyright    Copyright (c) 2023 Kamil Smolak
 */

namespace SmolCms\Bundle\ContentBlock\Type;

use SmolCms\Bundle\ContentBlock\Metadata\BlockMetadata;
use SmolCms\Bundle\ContentBlock\ResolvedBlock;
use SmolCms\Bundle\ContentBlock\ResolvedProperty;
use SmolCms\Bundle\ContentBlock\Type\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormTypeGuesserInterface;

readonly class BuiltinTypeHandler implements BlockTypeHandlerInterface, PropertyTypeHandlerInterface
{
    public function __construct(
        private FormTypeGuesserInterface $formTypeGuesser,
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
        if (!$type instanceof GenericType) {
            throw new Exception\UnexpectedTypeException($type, GenericType::class);
        }

        $builder->add('value', TextType::class, [
            'label' => $block->getLabel(),
            'required' => $block->isRequired(),
        ]);
    }

    /**
     * @throws UnexpectedTypeException
     */
    public function buildFormForProperty(ResolvedProperty $property, FormBuilderInterface $builder): void
    {
        $type = $property->getType();
        if (!$type instanceof BuiltinType) {
            throw new Exception\UnexpectedTypeException($type, BuiltinType::class);
        }

        $name = $property->getPropertyName();
        $formOptions = [
            'label' => $property->getLabel(),
            'required' => $property->isRequired(),
        ];

        if ($type->guess) {
            $typeGuess = $this->formTypeGuesser->guessType($property->getClass(), $name);

            if ($typeGuess) {
                $builder->add($name, $typeGuess->getType(), $formOptions + $typeGuess->getOptions());

                return;
            }
        }

        $propertyType = $type->type ?? $property->getMetadata()->getPropertyType();

        switch ($propertyType) {
            case 'string':
                $builder->add($name, TextType::class, $formOptions);
                return;

            case 'int':
                $builder->add($name, NumberType::class, $formOptions + [
                    'scale' => 0,
                ]);
                return;

            case 'float':
                $builder->add($name, NumberType::class, $formOptions);
                return;

            case 'bool':
                $builder->add($name, CheckboxType::class, $formOptions);
                return;
        }
    }
}
