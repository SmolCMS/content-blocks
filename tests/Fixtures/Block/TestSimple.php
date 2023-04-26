<?php

/*
 * @author       Kamil Smolak <kamil@smol.pl>
 * @link         http://www.smol.pl
 * @copyright    Copyright (c) 2023 Kamil Smolak
 */

namespace SmolCms\Bundle\ContentBlock\Tests\Fixtures\Block;

use SmolCms\Bundle\ContentBlock\Attribute\AsContentBlock;
use SmolCms\Bundle\ContentBlock\Attribute\Property;
use SmolCms\Bundle\ContentBlock\Type\UseFormType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

#[AsContentBlock('test_simple')]
class TestSimple
{
    #[Property]
    public string $string;
    #[Property]
    public ?string $stringNullable;
    #[Property]
    public TestSimple2 $innerSimple;
    #[Property(new UseFormType(TextareaType::class))]
    public string $formOnProperty;
}
