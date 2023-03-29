<?php

/*
 * @author       Kamil Smolak <kamil@smol.pl>
 * @link         http://www.smol.pl
 * @copyright    Copyright (c) 2022 Kamil Smolak
 */

namespace SmolCms\Bundle\ContentBlock\Attribute;

use Attribute;
use SmolCms\Bundle\ContentBlock\Type\GenericType;
use SmolCms\Bundle\ContentBlock\Type\TypeInterface;

#[Attribute(Attribute::TARGET_CLASS)]
class AsContentBlock
{
    public function __construct(
        public string $name,
        public ?string $label = null,
        public TypeInterface $type = new GenericType(),
        public array $provides = [],
    ) {
        $this->label ??= $this->name;
    }

    public function serviceConfig(): array
    {
        return [
            'key' => $this->name,
        ];
    }
}
