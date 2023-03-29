<?php

/*
 * @author       Kamil Smolak <kamil@smol.pl>
 * @link         http://www.smol.pl
 * @copyright    Copyright (c) 2023 Kamil Smolak
 */

namespace SmolCms\Bundle\ContentBlock\Metadata;

use SmolCms\Bundle\ContentBlock\Type\TypeInterface;

class BlockMetadata
{
    public function __construct(
        public readonly string $name,
        public readonly string $label,
        public readonly string $class,
        public readonly TypeInterface $type,
    ) {
    }
}
