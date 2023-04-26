<?php

/*
 * @author       Kamil Smolak <kamil@smol.pl>
 * @link         http://www.smol.pl
 * @copyright    Copyright (c) 2023 Kamil Smolak
 */

namespace SmolCms\Bundle\ContentBlock\Metadata;

use SmolCms\Bundle\ContentBlock\Type\TypeInterface;

readonly class BlockMetadata
{
    public function __construct(
        public string $name,
        public string $label,
        public string $class,
        public TypeInterface $type,
        public array $provides = [],
        public ?string $renderAs = null,
    ) {
    }
}
