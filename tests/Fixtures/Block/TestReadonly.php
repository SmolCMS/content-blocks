<?php

namespace SmolCms\Bundle\ContentBlock\Tests\Fixtures\Block;

use SmolCms\Bundle\ContentBlock\Attribute\AsContentBlock;
use SmolCms\Bundle\ContentBlock\Attribute\Property;

#[AsContentBlock('test_readonly')]
readonly class TestReadonly
{
    public function __construct(
        #[Property]
        public string $string,
    ) {
    }
}
