<?php

namespace SmolCms\Bundle\ContentBlock\Type;

readonly class ProvideType extends GenericType
{
    public function __construct(
        public array $allowed = [],
        public bool $allowChange = false,
        ?string $label = null,
        ?bool $required = null,
        public array $blockSelectorOptions = [],
    ) {
        parent::__construct(
            label: $label,
            required: $required,
            usePropertyForm: true,
        );
    }

    public function getHandler(): string
    {
        return ProvideTypeHandler::class;
    }
}
