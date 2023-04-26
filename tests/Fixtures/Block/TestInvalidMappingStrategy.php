<?php

/*
 * @author       Kamil Smolak <kamil@smol.pl>
 * @link         http://www.smol.pl
 * @copyright    Copyright (c) 2023 Kamil Smolak
 */

namespace SmolCms\Bundle\ContentBlock\Tests\Fixtures\Block;

use SmolCms\Bundle\ContentBlock\Attribute\AsContentBlock;
use SmolCms\Bundle\ContentBlock\Attribute\Property;
use SmolCms\Bundle\ContentBlock\Mapper\InvalidMappingStrategy;
use SmolCms\Bundle\ContentBlock\Type\ProvideType;

#[AsContentBlock('test_invalid_mapping_strategy')]
class TestInvalidMappingStrategy
{
    #[Property(new ProvideType(allowChange: true), invalidMappingStrategy: InvalidMappingStrategy::THROW)]
    public ?TestBlockInterface $throw = null;

    #[Property(new ProvideType(allowChange: true), invalidMappingStrategy: InvalidMappingStrategy::ERROR)]
    public ?TestBlockInterface $error = null;

    #[Property(new ProvideType(allowChange: true), invalidMappingStrategy: InvalidMappingStrategy::IGNORE)]
    public ?TestBlockInterface $ignore = null;
}
