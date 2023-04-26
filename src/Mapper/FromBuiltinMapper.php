<?php

/*
 * @author       Kamil Smolak <kamil@smol.pl>
 * @link         http://www.smol.pl
 * @copyright    Copyright (c) 2023 Kamil Smolak
 */

namespace SmolCms\Bundle\ContentBlock\Mapper;

use SmolCms\Bundle\ContentBlock\Type\Builtin;

class FromBuiltinMapper implements MapperInterface
{
    public function map(mixed $from, string $to): string|int|float
    {
        assert($from instanceof Builtin);

        return self::cast($to, $from->value);
    }

    public function supports(mixed $from, string $to): bool
    {
        return is_a($from, Builtin::class, true) && Builtin::supports($to);
    }

    public static function cast(string $to, mixed $value): mixed
    {
        return match($to) {
            'string' => (string)$value,
            'int' => (int)$value,
            'float' => (float)$value,
            'bool' => (bool)$value,
            default => $value,
        };
    }
}
