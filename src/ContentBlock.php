<?php

/*
 * @author       Kamil Smolak <kamil@smol.pl>
 * @link         http://www.smol.pl
 * @copyright    Copyright (c) 2022 Kamil Smolak
 */

namespace SmolCms\Bundle\ContentBlock;

use SmolCms\Bundle\ContentBlock\Metadata\BlockMetadata;

class ContentBlock
{
    public function __construct(
        private readonly BlockMetadata $metadata,
        private mixed $properties = null,
    ) {
    }

    public function getMetadata(): BlockMetadata
    {
        return $this->metadata;
    }

    public function getProperties(): mixed
    {
        return $this->properties;
    }

    public function setProperties(mixed $properties): void
    {
        $this->properties = $properties;
    }
}
