<?php

namespace SmolCms\Bundle\ContentBlock\Tests\Fixtures\Block;

use SmolCms\Bundle\ContentBlock\Attribute\AsContentBlock;
use SmolCms\Bundle\ContentBlock\Attribute\Property;

#[AsContentBlock('test_builtin')]
class TestBuiltin
{
    #[Property]
    public string $string;
    #[Property]
    public int $int;
    #[Property]
    public float $float;
    #[Property]
    public bool $bool;
}
