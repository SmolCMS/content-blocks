<?php

/*
 * @author       Kamil Smolak <kamil@smol.pl>
 * @link         http://www.smol.pl
 * @copyright    Copyright (c) 2023 Kamil Smolak
 */

namespace SmolCms\Bundle\ContentBlock\Mapper;

interface MapperInterface
{
    public function map(mixed $from, string $to): mixed;
    public function supports(mixed $from, string $to): bool;
}
