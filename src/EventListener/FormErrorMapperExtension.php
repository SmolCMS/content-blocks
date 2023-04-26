<?php

namespace SmolCms\Bundle\ContentBlock\EventListener;

use Symfony\Component\Form\AbstractExtension;

class FormErrorMapperExtension extends AbstractExtension
{
    protected function loadTypeExtensions(): array
    {
        return [
            new FormErrorMapperTypeExtension(),
        ];
    }
}
