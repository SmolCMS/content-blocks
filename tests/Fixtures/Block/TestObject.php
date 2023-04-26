<?php

namespace SmolCms\Bundle\ContentBlock\Tests\Fixtures\Block;

use SmolCms\Bundle\ContentBlock\Attribute\AsContentBlock;
use SmolCms\Bundle\ContentBlock\Attribute\Property;
use SmolCms\Bundle\ContentBlock\Type\BuiltinType;
use SmolCms\Bundle\ContentBlock\Type\UseFormType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Serializer\Annotation\Context;

#[AsContentBlock('test_object')]
class TestObject
{
    #[Property(type: new UseFormType(DateTimeType::class, ['widget' => 'single_text']), denormalize: \DateTimeInterface::class)]
    public object $object;

    #[Property(type: new UseFormType(DateTimeType::class, ['widget' => 'single_text']), denormalize: \DateTimeInterface::class)]
    public \DateTimeInterface $dateTime;
}
