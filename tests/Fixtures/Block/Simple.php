<?php

/*
 * @author       Kamil Smolak <kamil@smol.pl>
 * @link         http://www.smol.pl
 * @copyright    Copyright (c) 2023 Kamil Smolak
 */

namespace SmolCms\Bundle\ContentBlock\Tests\Fixtures\Block;

use SmolCms\Bundle\ContentBlock\Attribute\AsContentBlock;
use SmolCms\Bundle\ContentBlock\Attribute\Property;
use SmolCms\Bundle\ContentBlock\ContentBlock\Group;
use SmolCms\Bundle\ContentBlock\Type\GenericType;
use SmolCms\Bundle\ContentBlock\Type\GroupProvideHandler;
use SmolCms\Bundle\ContentBlock\Type\GroupProvideType;
use SmolCms\Bundle\ContentBlock\Type\ProvideHandler;
use SmolCms\Bundle\ContentBlock\Type\ProvideType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

#[AsContentBlock('simple')]
class Simple
{
    #[Property]
    public string $string;
    #[Property]
    public ?string $stringNullable;
    #[Property(new GenericType(handler: OtherHandler::class))]
    public string $handlerOnProperty;
    #[Property(new GenericType(handler: OtherHandler::class))]
    public Simple2 $handlerOnProperty2;
    #[Property]
    public Simple2 $innerSimple;
    #[Property]
    public Group $group;
    #[Property]
    public array $groupArray;
    #[Property(new GroupProvideType([Simple2::class]))]
    public Group $groupProvide;
    #[Property(new ProvideType())]
    public string $provideString;
    #[Property(new GenericType(TextType::class))]
    public Simple2 $formOnProperty;
}
