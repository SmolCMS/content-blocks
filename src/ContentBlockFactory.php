<?php
/*
 * @author       Kamil Smolak <kamil@smol.pl>
 * @link         http://www.smol.pl
 * @copyright    Copyright (c) 2023 Kamil Smolak
 */

namespace SmolCms\Bundle\ContentBlock;

use SmolCms\Bundle\ContentBlock\Metadata\ContentBlockRegistry;

class ContentBlockFactory
{
    public function __construct(
        private readonly ContentBlockRegistry $registry,
    ) {
    }

    public function create(array $block, ?string $blockName = null): ContentBlock
    {
        $blockName ??= $block['name'];
        $metadata = $this->registry->metadataFor($blockName);

        return new ContentBlock($metadata, $block['properties']);
    }
}
