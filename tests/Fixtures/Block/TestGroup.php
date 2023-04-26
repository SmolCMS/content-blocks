<?php

/*
 * @author       Kamil Smolak <kamil@smol.pl>
 * @link         http://www.smol.pl
 * @copyright    Copyright (c) 2023 Kamil Smolak
 */

namespace SmolCms\Bundle\ContentBlock\Tests\Fixtures\Block;

use SmolCms\Bundle\ContentBlock\Attribute\AsContentBlock;
use SmolCms\Bundle\ContentBlock\Attribute\Property;
use SmolCms\Bundle\ContentBlock\Type\Group;
use SmolCms\Bundle\ContentBlock\Type\GroupType;

#[AsContentBlock('test_group')]
class TestGroup
{
    #[Property(new GroupType())]
    public array $array;
    #[Property(new GroupType())]
    public iterable $iterable;
    #[Property]
    public Group $group;
}
