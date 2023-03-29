<?php

/*
 * @author       Kamil Smolak <kamil@smol.pl>
 * @link         http://www.smol.pl
 * @copyright    Copyright (c) 2023 Kamil Smolak
 */

namespace SmolCms\Bundle\ContentBlock\Mapper;

use SmolCms\Bundle\ContentBlock\ContentBlock\Builtin;

class BuiltinMapper implements MapperInterface
{
    public function map(mixed $from, string $to): string|int|float
    {
        assert($from instanceof Builtin);

        return match ($to) {
            'string' => (string)$from->value,
            'int' => (int)$from->value,
            'float' => (float)$from->value,
            default => $from->value,
        };
    }

    public function supports(mixed $from, string $to): bool
    {
        return is_a($from, Builtin::class, true) && Builtin::supports($to);
    }
}
