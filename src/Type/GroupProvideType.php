<?php

namespace SmolCms\Bundle\ContentBlock\Type;

readonly class GroupProvideType extends GenericType
{
    public function __construct(
        public array $allowed = [],
        public bool $allowChange = false,
        array $formOptions = [],
        ?string $label = null,
        ?bool $required = null,
    ) {
        parent::__construct(
            formOptions: $formOptions,
            label: $label,
            required: $required,
            handler: GroupProvideHandler::class,
        );
    }
}
