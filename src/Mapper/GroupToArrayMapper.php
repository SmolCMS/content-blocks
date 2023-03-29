<?php

/*
 * @author       Kamil Smolak <kamil@smol.pl>
 * @link         http://www.smol.pl
 * @copyright    Copyright (c) 2023 Kamil Smolak
 */

namespace SmolCms\Bundle\ContentBlock\Mapper;

use SmolCms\Bundle\ContentBlock\ContentBlock\Group;

class GroupToArrayMapper implements MapperInterface
{
    public function map(mixed $from, string $to): array
    {
        assert($from instanceof Group);

        return $from->items;
    }

    public function supports(mixed $from, string $to): bool
    {
        return is_a($from, Group::class, true) && $to === 'array';
    }
}
