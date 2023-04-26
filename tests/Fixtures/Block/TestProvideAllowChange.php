<?php

/*
 * @author       Kamil Smolak <kamil@smol.pl>
 * @link         http://www.smol.pl
 * @copyright    Copyright (c) 2023 Kamil Smolak
 */

namespace SmolCms\Bundle\ContentBlock\Tests\Fixtures\Block;

use SmolCms\Bundle\ContentBlock\Attribute\AsContentBlock;
use SmolCms\Bundle\ContentBlock\Attribute\Property;
use SmolCms\Bundle\ContentBlock\Type\ProvideType;

#[AsContentBlock('test_provide_allow_change')]
class TestProvideAllowChange
{
    #[Property(new ProvideType(allowChange: true))]
    public TestBlockInterface $allowChange;
}
