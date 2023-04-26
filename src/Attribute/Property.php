<?php

/*
 * @author       Kamil Smolak <kamil@smol.pl>
 * @link         http://www.smol.pl
 * @copyright    Copyright (c) 2022 Kamil Smolak
 */

namespace SmolCms\Bundle\ContentBlock\Attribute;

use Attribute;
use SmolCms\Bundle\ContentBlock\Mapper\InvalidMappingStrategy;
use SmolCms\Bundle\ContentBlock\Type\TypeInterface;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Property
{
    public function __construct(
        public ?TypeInterface $type = null,
        public ?string $denormalize = null,
        public InvalidMappingStrategy $invalidMappingStrategy = InvalidMappingStrategy::THROW,
    ) {
    }
}
