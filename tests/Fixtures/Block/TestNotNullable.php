<?php

namespace SmolCms\Bundle\ContentBlock\Tests\Fixtures\Block;

use SmolCms\Bundle\ContentBlock\Attribute\AsContentBlock;
use SmolCms\Bundle\ContentBlock\Attribute\Property;

#[AsContentBlock('test_not_nullable')]
class TestNotNullable
{
    #[Property]
    public string $foo;
}
