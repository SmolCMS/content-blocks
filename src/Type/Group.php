<?php

/*
 * @author       Kamil Smolak <kamil@smol.pl>
 * @link         http://www.smol.pl
 * @copyright    Copyright (c) 2023 Kamil Smolak
 */

namespace SmolCms\Bundle\ContentBlock\Type;

use SmolCms\Bundle\ContentBlock\Attribute\AsContentBlock;
use SmolCms\Bundle\ContentBlock\Attribute\Property;
use Traversable;

#[AsContentBlock('group', type: new GroupType())]
class Group implements \IteratorAggregate, \Countable
{
    public function __construct(
        #[Property]
        public array $items = [],
    ) {
    }

    public static function supports(string $to): bool
    {
        return in_array($to, ['array', 'iterable']);
    }

    public function getIterator(): Traversable
    {
        return new \ArrayIterator($this->items);
    }

    public function count(): int
    {
        return count($this->items);
    }
}
