<?php

namespace SmolCms\Bundle\ContentBlock\Tests\Fixtures\Block;

use SmolCms\Bundle\ContentBlock\Attribute\AsContentBlock;
use SmolCms\Bundle\ContentBlock\Attribute\Property;
use Symfony\Component\Validator\Constraints as Assert;

#[AsContentBlock('test_validation_inner')]
class TestValidationInner
{
    #[Assert\NotBlank]
    #[Property]
    public string $foo;
}
