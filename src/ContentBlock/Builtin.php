<?php

/*
 * @author       Kamil Smolak <kamil@smol.pl>
 * @link         http://www.smol.pl
 * @copyright    Copyright (c) 2023 Kamil Smolak
 */

namespace SmolCms\Bundle\ContentBlock\ContentBlock;

use SmolCms\Bundle\ContentBlock\Attribute\AsContentBlock;
use SmolCms\Bundle\ContentBlock\Attribute\Property;
use SmolCms\Bundle\ContentBlock\Type\BuiltinHandler;
use SmolCms\Bundle\ContentBlock\Type\GenericType;

#[AsContentBlock('builtin', type: new GenericType(handler: BuiltinHandler::class, usePropertyForm: true))]
class Builtin
{
    #[Property]
    public mixed $value = null;

    public static function supports(string $propertyType): bool
    {
        return in_array($propertyType, ['string', 'int', 'float', 'mixed'], true);
    }
}
