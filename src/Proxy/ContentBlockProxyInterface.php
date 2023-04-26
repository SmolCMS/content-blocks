<?php

namespace SmolCms\Bundle\ContentBlock\Proxy;

use SmolCms\Bundle\ContentBlock\Metadata\BlockMetadata;

interface ContentBlockProxyInterface
{
    public function __getMetadata(): BlockMetadata;

    public function __setMetadata(BlockMetadata $metadata): void;
}
