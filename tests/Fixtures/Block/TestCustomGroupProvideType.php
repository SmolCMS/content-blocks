<?php

namespace SmolCms\Bundle\ContentBlock\Tests\Fixtures\Block;

use SmolCms\Bundle\ContentBlock\Type\GroupProvideType;

readonly class TestCustomGroupProvideType extends GroupProvideType
{
    public function __construct() {
        parent::__construct(allowed: [
            TestSimple::class,
            TestSimple2::class,
        ]);
    }
}
