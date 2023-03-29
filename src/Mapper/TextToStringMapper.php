<?php
/*
 * @author       Kamil Smolak <kamil@smol.pl>
 * @link         http://www.smol.pl
 * @copyright    Copyright (c) 2023 Kamil Smolak
 */

namespace SmolCms\Bundle\ContentBlock\Mapper;

use SmolCms\Bundle\ContentBlock\ContentBlock\Text;

class TextToStringMapper implements MapperInterface
{
    public function map(mixed $from, string $to): string
    {
        assert($from instanceof Text);

        return $from->text;
    }

    public function supports(mixed $from, string $to): bool
    {
        return is_a($from, Text::class) && $to === 'string';
    }
}
