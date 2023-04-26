<?php

/*
 * @author       Kamil Smolak <kamil@smol.pl>
 * @link         http://www.smol.pl
 * @copyright    Copyright (c) 2023 Kamil Smolak
 */

namespace SmolCms\Bundle\ContentBlock\Type;

use SmolCms\Bundle\ContentBlock\ResolvedProperty;
use SmolCms\Bundle\ContentBlock\Type\Exception\UnexpectedTypeException;
use Symfony\Component\Form\FormBuilderInterface;

class UseFormTypeHandler implements PropertyTypeHandlerInterface
{
    /**
     * @throws UnexpectedTypeException
     */
    public function buildFormForProperty(ResolvedProperty $property, FormBuilderInterface $builder): void
    {
        $type = $property->getType();
        if (!$type instanceof UseFormType) {
            throw new Exception\UnexpectedTypeException($type, UseFormType::class);
        }

        $builder->add($property->getPropertyName(), $type->formType, $type->formOptions + [
            'label' => $property->getLabel(),
            'required' => $property->isRequired(),
        ]);
    }
}
