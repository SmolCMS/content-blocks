<?php

namespace SmolCms\Bundle\ContentBlock\Type;

use SmolCms\Bundle\ContentBlock\Metadata\BlockMetadata;

interface TypeHandlerInterface
{
    public function initializeObject(BlockMetadata $blockMetadata): ?object;
}
