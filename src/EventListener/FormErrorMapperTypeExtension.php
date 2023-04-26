<?php

namespace SmolCms\Bundle\ContentBlock\EventListener;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;

class FormErrorMapperTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventSubscriber(new FormErrorMapperListener());
    }

    public static function getExtendedTypes(): iterable
    {
        return [FormType::class];
    }
}
