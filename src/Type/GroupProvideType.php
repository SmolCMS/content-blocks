<?php

namespace SmolCms\Bundle\ContentBlock\Type;

use Symfony\Component\Form\Extension\Core\Type\CollectionType;

readonly class GroupProvideType extends GroupType
{
    public function __construct(
        array $allowed = [],
        bool $allowChange = false,
        string $formType = CollectionType::class,
        array $formOptions = [],
        ?string $label = null,
        ?bool $required = null
    ) {
        parent::__construct($allowed, $allowChange, true, $formType, $formOptions, $label, $required);
    }
}
