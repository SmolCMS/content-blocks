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
        private mixed $data = null,
    ) {
    }

    public function getMetadata(): BlockMetadata
    {
        return $this->metadata;
    }

    public function getData(): mixed
    {
        return $this->data;
    }

    public function setData(mixed $data): void
    {
        $this->data = $data;
    }
}
