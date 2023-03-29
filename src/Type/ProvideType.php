<?php

namespace SmolCms\Bundle\ContentBlock\Type;

readonly class ProvideType extends GenericType
{
    public function __construct(
        public array $allowed = [],
        public bool $allowChange = false,
    ) {
        parent::__construct(handler: ProvideHandler::class, usePropertyForm: true);
    }
}
