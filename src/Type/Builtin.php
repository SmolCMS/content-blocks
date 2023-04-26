<?php

/*
 * @author       Kamil Smolak <kamil@smol.pl>
 * @link         http://www.smol.pl
 * @copyright    Copyright (c) 2023 Kamil Smolak
 */

namespace SmolCms\Bundle\ContentBlock\Type;

use SmolCms\Bundle\ContentBlock\Attribute\AsContentBlock;
use SmolCms\Bundle\ContentBlock\Attribute\Property;

#[AsContentBlock('builtin', type: new BuiltinType())]
class Builtin
{
    #[Property(type: new BuiltinType())]
    public string|int|float|bool|null $value = null;

    public static function supports(string $propertyType): bool
    {
        return in_array($propertyType, ['string', 'int', 'float', 'bool', 'array', 'iterable'], true);
    }
}
