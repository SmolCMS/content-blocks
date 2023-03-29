<?php

/*
 * @author       Kamil Smolak <kamil@smol.pl>
 * @link         http://www.smol.pl
 * @copyright    Copyright (c) 2023 Kamil Smolak
 */

namespace SmolCms\Bundle\ContentBlock\ContentBlock;

use SmolCms\Bundle\ContentBlock\Attribute\AsContentBlock;
use SmolCms\Bundle\ContentBlock\Attribute\Property;
use Symfony\Component\Validator\Constraints as Assert;

#[AsContentBlock('text', provides: ['string'])]
class Text
{
    #[Property]
    #[Assert\NotBlank]
    public string $text;
}
