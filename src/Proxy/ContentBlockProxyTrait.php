<?php

namespace SmolCms\Bundle\ContentBlock\Proxy;

use SmolCms\Bundle\ContentBlock\Metadata\BlockMetadata;

trait ContentBlockProxyTrait
{
    private BlockMetadata $__metadata;

    public function __getMetadata(): BlockMetadata
    {
        return $this->__metadata;
    }

    public function __setMetadata(BlockMetadata $metadata): void
    {
        $this->__metadata = $metadata;
    }
}
