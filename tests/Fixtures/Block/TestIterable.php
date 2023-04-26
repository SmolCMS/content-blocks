<?php

namespace SmolCms\Bundle\ContentBlock\Tests\Fixtures\Block;

use SmolCms\Bundle\ContentBlock\Attribute\AsContentBlock;
use SmolCms\Bundle\ContentBlock\Attribute\Property;

#[AsContentBlock('test_iterable')]
class TestIterable
{
    public function __construct(
        #[Property]
        public iterable $iterable,
        #[Property]
        public array $array,
    ) {
    }
}
