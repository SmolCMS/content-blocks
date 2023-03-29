<?php
/*
 * @author       Kamil Smolak <kamil@smol.pl>
 * @link         http://www.smol.pl
 * @copyright    Copyright (c) 2023 Kamil Smolak
 */

namespace SmolCms\Bundle\ContentBlock\ContentBlock;

use SmolCms\Bundle\ContentBlock\Attribute\AsContentBlock;
use SmolCms\Bundle\ContentBlock\Attribute\Property;
use SmolCms\Bundle\ContentBlock\Type\GenericType;
use SmolCms\Bundle\ContentBlock\Type\GuessHandler;
use Symfony\Component\Validator\Constraints as Assert;

#[AsContentBlock('link')]
class Link
{
    #[Property(new GenericType(handler: GuessHandler::class, usePropertyForm: true))]
    #[Assert\Url]
    public string $url;

    #[Property(new GenericType(handler: GuessHandler::class, usePropertyForm: true))]
    #[Assert\Type('string')]
    #[Assert\Length(min: 3)]
    public string $label;
}
