<?php

namespace SmolCms\Bundle\ContentBlock\Tests\Fixtures\Block;

use SmolCms\Bundle\ContentBlock\Attribute\AsContentBlock;
use SmolCms\Bundle\ContentBlock\Attribute\Property;

#[AsContentBlock('test_interface2', provides: [TestBlockInterface::class])]
class TestBlockInterface2 implements TestBlockInterface {
    public function __construct(
        #[Property]
        public ?string $foo2 = null
    ) {
    }
}
