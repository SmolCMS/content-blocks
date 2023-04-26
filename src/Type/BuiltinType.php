<?php

namespace SmolCms\Bundle\ContentBlock\Type;

readonly class BuiltinType extends GenericType
{
    public function __construct(
        public ?string $type = null,
        bool|string|null $label = null,
        ?bool $required = null,
        public bool $guess = true,
    ) {
        parent::__construct(
            label: $label,
            required: $required,
            usePropertyForm: true,
        );
    }

    public function getHandler(): string
    {
        return BuiltinTypeHandler::class;
    }
}
