<?php

/*
 * @author       Kamil Smolak <kamil@smol.pl>
 * @link         http://www.smol.pl
 * @copyright    Copyright (c) 2023 Kamil Smolak
 */

namespace SmolCms\Bundle\ContentBlock\Mapper;

use UnexpectedValueException;

class CompositeMapper implements MapperInterface
{
    /**
     * @param MapperInterface[] $mappers
     */
    public function __construct(
        private readonly iterable $mappers,
    ) {
    }

    public function map(mixed $from, string $to): mixed
    {
        foreach ($this->mappers as $mapper) {
            if (!$mapper->supports($from, $to)) {
                continue;
            }

            return $mapper->map($from, $to);
        }

        throw new UnexpectedValueException(
            sprintf('Could not map block from "%s" to "%s".', get_debug_type($from), $to)
        );
    }

    public function supports(mixed $from, string $to): bool
    {
        foreach ($this->mappers as $mapper) {
            if (!$mapper->supports($from, $to)) {
                continue;
            }

            return true;
        }

        return false;
    }
}
