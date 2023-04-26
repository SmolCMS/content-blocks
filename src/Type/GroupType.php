<?php

namespace SmolCms\Bundle\ContentBlock\Type;

use Symfony\Component\Form\Extension\Core\Type\CollectionType;

readonly class GroupType extends GenericType
{
    public function __construct(
        private ?array $allowed = null,
        public bool $allowChange = false,
        public bool $provide = false,
        public string $formType = CollectionType::class,
        public array $formOptions = [],
        public array $blockSelectorOptions = [],
        ?string $label = null,
        ?bool $required = null,
    ) {
        parent::__construct(
            label: $label,
            required: $required,
            usePropertyForm: true,
        );
    }

    public function getAllowedBlocks(): ?array
    {
        return $this->allowed;
    }

    public function getHandler(): string
    {
        return GroupTypeHandler::class;
    }
}
