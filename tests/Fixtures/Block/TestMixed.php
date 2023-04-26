<?php

namespace SmolCms\Bundle\ContentBlock\Tests\Fixtures\Block;

use SmolCms\Bundle\ContentBlock\Attribute\AsContentBlock;
use SmolCms\Bundle\ContentBlock\Attribute\Property;
use SmolCms\Bundle\ContentBlock\Type\BuiltinType;

#[AsContentBlock('test_mixed')]
class TestMixed
{
    #[Property(type: new BuiltinType(type: 'string'), denormalize: 'string')]
    public $emptyType;
    #[Property(type: new BuiltinType(type: 'string'), denormalize: 'string')]
    public mixed $mixedType;
    #[Property(type: new BuiltinType(type: 'string'), denormalize: 'string')]
    public string|int|float $unionType;
}
