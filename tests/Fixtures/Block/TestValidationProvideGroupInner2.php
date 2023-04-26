<?php

namespace SmolCms\Bundle\ContentBlock\Tests\Fixtures\Block;

use SmolCms\Bundle\ContentBlock\Attribute\AsContentBlock;
use SmolCms\Bundle\ContentBlock\Attribute\Property;
use SmolCms\Bundle\ContentBlock\Type\GroupType;
use Symfony\Component\Validator\Constraints as Assert;

#[AsContentBlock('test_validation_provide_group_inner2')]
class TestValidationProvideGroupInner2
{
    #[Assert\Count(min: 1)]
    #[Property(new GroupType([TestBlockInterface1::class, TestBlockInterface2::class]))]
    public array $items2;
}
