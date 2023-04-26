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

#[AsContentBlock('test_provide')]
class TestProvide
{
    #[Property(new ProvideType())]
    public string $string;

    #[Property(new ProvideType())]
    public TestSimple2 $compound;

    #[Property(new ProvideType(allowed: [TestSimple2::class]))]
    public $emptyTypeAllowed;

    #[Property(new ProvideType())]
    public TestBlockInterface $interface;

    //todo sprawdzić błąd

//    #[Property(new ProvideType())]
//    public $emptyType;
}
