<?php

/*
 * @author       Kamil Smolak <kamil@smol.pl>
 * @link         http://www.smol.pl
 * @copyright    Copyright (c) 2023 Kamil Smolak
 */

namespace SmolCms\Bundle\ContentBlock\Mapper;

use SmolCms\Bundle\ContentBlock\Type\Builtin;

class ToBuiltinMapper implements MapperInterface
{
    public function map(mixed $from, string $to): Builtin
    {
        $item = new Builtin();
        $item->value = $from;

        return $item;
    }

    public function supports(mixed $from, string $to): bool
    {
        return Builtin::supports(gettype($from)) && is_a($to, Builtin::class, true);
    }
}
