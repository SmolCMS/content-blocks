<?php

/*
 * @author       Kamil Smolak <kamil@smol.pl>
 * @link         http://www.smol.pl
 * @copyright    Copyright (c) 2023 Kamil Smolak
 */

namespace SmolCms\Bundle\ContentBlock\Type;

use SmolCms\Bundle\ContentBlock\ResolvedProperty;
use SmolCms\Bundle\ContentBlock\Type\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormTypeGuesserInterface;

class GuessHandler extends AbstractHandler
{
    public function __construct(
        private readonly FormTypeGuesserInterface $formTypeGuesser,
    ) {
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

        if ($type = $this->formTypeGuesser->guessType($property->getClass(), $property->getPropertyName())) {
            $builder->add($property->getPropertyName(), $type->getType(), $formOptions + $type->getOptions());
            return;
        }

        switch ($property->getMetadata()->getPropertyType()) {
            case 'string':
                $builder->add($property->getPropertyName(), TextType::class, $formOptions);
                return;

            case 'int':
                $builder->add($property->getPropertyName(), NumberType::class, $formOptions + [
                    'scale' => 0,
                ]);
                return;

            case 'float':
                $builder->add($property->getPropertyName(), NumberType::class, $formOptions);
                return;

            case 'bool':
                $builder->add($property->getPropertyName(), CheckboxType::class, $formOptions);
                return;
        }
    }
}
