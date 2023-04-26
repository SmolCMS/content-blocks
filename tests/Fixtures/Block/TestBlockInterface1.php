<?php

namespace SmolCms\Bundle\ContentBlock\Tests\Fixtures\Block;

use SmolCms\Bundle\ContentBlock\Attribute\AsContentBlock;
use SmolCms\Bundle\ContentBlock\Attribute\Property;

#[AsContentBlock('test_interface1', provides: [TestBlockInterface::class])]
class TestBlockInterface1 implements TestBlockInterface {
    public function __construct(
        #[Property]
        public ?string $foo1 = null,
    ) {
    }
}
