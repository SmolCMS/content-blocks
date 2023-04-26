<?php

namespace SmolCms\Bundle\ContentBlock\Type;

use SmolCms\Bundle\ContentBlock\ResolvedProperty;
use Symfony\Component\Form\FormBuilderInterface;

interface PropertyTypeHandlerInterface
{
    public function buildFormForProperty(ResolvedProperty $property, FormBuilderInterface $builder): void;
}
