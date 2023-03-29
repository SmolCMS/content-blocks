<?php

namespace SmolCms\Bundle\ContentBlock\Type;

readonly class GroupType extends GenericType
{
    public function __construct(
        public array $allowed = [],
        array $formOptions = [],
        ?string $label = null,
        ?bool $required = null,
    ) {
        parent::__construct(
            formOptions: $formOptions,
            label: $label,
            required: $required,
            handler: GroupHandler::class,
        );
    }
}
