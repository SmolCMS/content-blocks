<?php

/*
 * @author       Kamil Smolak <kamil@smol.pl>
 * @link         http://www.smol.pl
 * @copyright    Copyright (c) 2023 Kamil Smolak
 */

namespace SmolCms\Bundle\ContentBlock\Validator;

use Symfony\Component\Validator\Constraint;

class ContentBlock extends Constraint
{
    public function __construct(public readonly array $constraints)
    {
        parent::__construct();
    }
}
