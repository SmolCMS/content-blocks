<?php

namespace SmolCms\Bundle\ContentBlock\Tests\Fixtures\Block;

use SmolCms\Bundle\ContentBlock\Attribute\AsContentBlock;
use SmolCms\Bundle\ContentBlock\Attribute\Property;
use SmolCms\Bundle\ContentBlock\Type\GroupType;
use SmolCms\Bundle\ContentBlock\Type\ProvideType;
use Symfony\Component\Validator\Constraints as Assert;

#[AsContentBlock('test_validation')]
class TestValidation
{
    #[Assert\NotBlank]
    #[Property]
    public string $property;

    #[Assert\Valid]
    #[Property]
    public TestValidationInner $compound;

    #[Assert\NotBlank]
    #[Property(new ProvideType())]
    public string $provideSingle;

    #[Assert\NotBlank]
    #[Property(new ProvideType(allowed: [TestBlockInterface1::class, TestBlockInterface2::class]))]
    public TestBlockInterface $provide;

    #[Assert\Valid]
    #[Property(new ProvideType())]
    public TestValidationInner $provideCompound;

    #[Assert\NotBlank]
    #[Property(new GroupType(allowed: [TestValidationInner::class]))]
    public array $groupEmpty;

    #[Assert\NotBlank]
    #[Assert\Valid]
    #[Property(new GroupType(allowed: [TestValidationInner::class]))]
    public array $groupItemInvalid;
}
