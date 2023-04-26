<?php

/*
 * @author       Kamil Smolak <kamil@smol.pl>
 * @link         http://www.smol.pl
 * @copyright    Copyright (c) 2023 Kamil Smolak
 */

namespace SmolCms\Bundle\ContentBlock\Tests\Fixtures\Block;

use SmolCms\Bundle\ContentBlock\ResolvedProperty;
use SmolCms\Bundle\ContentBlock\Type\AbstractTypeHandler;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class TestCustomTypeHandler extends AbstractTypeHandler
{
    public function buildFormForProperty(ResolvedProperty $property, FormBuilderInterface $builder): void
    {
        $name = $property->getPropertyName();

        $builder->add(
            $builder->create($name, FormType::class)
                ->add('foo', TextType::class)
                ->add('bar', TextType::class)
                ->addModelTransformer(new CallbackTransformer())
        );
    }
}
