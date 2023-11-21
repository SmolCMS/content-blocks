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
        array $blockSelectorOptions = [],
        ?string $label = null,
        ?bool $required = null
    ) {
        parent::__construct(
            allowed: $allowed,
            allowChange: $allowChange,
            provide: true,
            formType: $formType,
            formOptions: $formOptions,
            blockSelectorOptions: $blockSelectorOptions,
            label: $label,
            required: $required,
        );
    }
}
