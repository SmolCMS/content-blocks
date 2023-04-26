<?php

namespace SmolCms\Bundle\ContentBlock\Tests\Fixtures\Block;

use SmolCms\Bundle\ContentBlock\Attribute\AsContentBlock;
use SmolCms\Bundle\ContentBlock\Attribute\Property;
use SmolCms\Bundle\ContentBlock\Proxy\ContentBlockProxyInterface;
use SmolCms\Bundle\ContentBlock\Proxy\ContentBlockProxyTrait;

#[AsContentBlock('test_proxy')]
class TestProxy implements ContentBlockProxyInterface
{
    use ContentBlockProxyTrait;

    public function __construct(
        #[Property]
        public ?string $prop = null,
    ) {
    }
}
