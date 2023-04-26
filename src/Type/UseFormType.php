<?php

namespace SmolCms\Bundle\ContentBlock\Type;

readonly class UseFormType extends GenericType
{
    public function __construct(
        public ?string $formType = null,
        public array $formOptions = [],
        string|bool|null $label = null,
        ?bool $required = null,
    ) {
        parent::__construct(
            label: $label,
            required: $required,
            usePropertyForm: true,
        );
    }

    public function getHandler(): string
    {
        return UseFormTypeHandler::class;
    }
}
