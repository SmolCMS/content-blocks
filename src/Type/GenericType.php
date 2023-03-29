<?php

namespace SmolCms\Bundle\ContentBlock\Type;

readonly class GenericType implements TypeInterface
{
    public function __construct(
        public ?string $formType = null,
        public array $formOptions = [],
        public string|bool|null $label = null,
        public ?bool $required = null,
        public string $handler = GenericHandler::class,
        public bool $usePropertyForm = false,
    ) {
    }

    public function getHandler(): string
    {
        return $this->handler;
    }
}
