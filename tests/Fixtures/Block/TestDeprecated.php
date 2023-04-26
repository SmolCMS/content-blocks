<?php

namespace SmolCms\Bundle\ContentBlock\Tests\Fixtures\Block;

use SmolCms\Bundle\ContentBlock\Attribute\AsContentBlock;
use SmolCms\Bundle\ContentBlock\Attribute\Property;

#[AsContentBlock('test_deprecated')]
class TestDeprecated
{
    #[Property]
    public $emptyType;
    #[Property]
    public mixed $mixedType;
    #[Property]
    public string|int|float $unionType;
}
