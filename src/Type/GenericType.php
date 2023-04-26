<?php

namespace SmolCms\Bundle\ContentBlock\Type;

readonly class GenericType implements TypeInterface
{
    public function __construct(
        public string|bool|null $label = null,
        public ?bool $required = null,
        public bool $usePropertyForm = false,
    ) {
    }

    public function getHandler(): string
    {
        return GenericTypeHandler::class;
    }
}
