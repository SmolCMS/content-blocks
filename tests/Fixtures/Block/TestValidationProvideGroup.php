<?php

namespace SmolCms\Bundle\ContentBlock\Tests\Fixtures\Block;

use SmolCms\Bundle\ContentBlock\Attribute\AsContentBlock;
use SmolCms\Bundle\ContentBlock\Attribute\Property;
use SmolCms\Bundle\ContentBlock\Type\ProvideType;
use Symfony\Component\Validator\Constraints as Assert;

#[AsContentBlock('test_validation_provide_group')]
class TestValidationProvideGroup
{
    #[Assert\Valid]
    #[Property(new ProvideType())]
    public TestValidationProvideGroupInner $inner;
}
