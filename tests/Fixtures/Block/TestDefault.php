<?php

/*
 * @author       Kamil Smolak <kamil@smol.pl>
 * @link         http://www.smol.pl
 * @copyright    Copyright (c) 2023 Kamil Smolak
 */

namespace SmolCms\Bundle\ContentBlock\Tests\Fixtures\Block;

use SmolCms\Bundle\ContentBlock\Attribute\AsContentBlock;
use SmolCms\Bundle\ContentBlock\Attribute\Property;

#[AsContentBlock('test_default')]
class TestDefault
{
    #[Property]
    public string $string = 'foo';
    #[Property]
    public ?string $null = null;
    #[Property]
    public TestSimple2 $compound;

    public function __construct()
    {
        $this->compound = new TestSimple2();
        $this->compound->string = 'foo';
        $this->compound->stringNullable = null;
    }
}
