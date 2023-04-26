<?php

/*
 * @author       Kamil Smolak <kamil@smol.pl>
 * @link         http://www.smol.pl
 * @copyright    Copyright (c) 2023 Kamil Smolak
 */

namespace SmolCms\Bundle\ContentBlock\Tests\Fixtures\Block;

use SmolCms\Bundle\ContentBlock\Attribute\AsContentBlock;
use SmolCms\Bundle\ContentBlock\Attribute\Property;
use SmolCms\Bundle\ContentBlock\Type\GroupProvideType;

#[AsContentBlock('test_group_provide')]
class TestGroupProvide
{
    #[Property(new GroupProvideType([TestSimple2::class]))]
    public array $property;

    #[Property(new TestCustomGroupProvideType())]
    public array $customType;
}
