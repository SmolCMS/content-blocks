<?php

/*
 * @author       Kamil Smolak <kamil@smol.pl>
 * @link         http://www.smol.pl
 * @copyright    Copyright (c) 2023 Kamil Smolak
 */

namespace SmolCms\Bundle\ContentBlock\Tests\Fixtures\Block;

use SmolCms\Bundle\ContentBlock\Attribute\AsContentBlock;
use SmolCms\Bundle\ContentBlock\Attribute\Property;
use SmolCms\Bundle\ContentBlock\Metadata\BlockMetadata;
use SmolCms\Bundle\ContentBlock\Type\GenericType;
use SmolCms\Bundle\ContentBlock\Type\GroupType;

#[AsContentBlock('test_group_allowed_custom')]
class TestGroupAllowedCustom
{
    #[Property(new GroupType(allowed: [
        new BlockMetadata('test_custom_proxy1', 'test_custom_proxy1', TestProxy::class, new GenericType()),
        new BlockMetadata('test_custom_proxy2', 'test_custom_proxy2', TestProxy::class, new GenericType()),
    ]))]
    public array $items;
}
