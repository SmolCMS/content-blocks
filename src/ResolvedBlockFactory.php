<?php

/*
 * @author       Kamil Smolak <kamil@smol.pl>
 * @link         http://www.smol.pl
 * @copyright    Copyright (c) 2023 Kamil Smolak
 */

namespace SmolCms\Bundle\ContentBlock;

use SmolCms\Bundle\ContentBlock\Metadata\BlockMetadataReader;
use SmolCms\Bundle\ContentBlock\Metadata\BlockMetadata;

readonly class ResolvedBlockFactory
{
    public function __construct(
        private ResolvedPropertyFactory $propertyFactory,
    ) {
    }

    public function create(?ResolvedProperty $property, BlockMetadata $blockMetadata): ResolvedBlock
    {
        return new ResolvedBlock(
            $property,
            $blockMetadata,
            $this->resolveProperties($blockMetadata),
        );
    }

    private function resolveProperties(BlockMetadata $metadata): array
    {
        $properties = [];
        $reader = new BlockMetadataReader($metadata->class);

        foreach ($reader->getProperties() as $propertyMetadata) {
            $properties[] = $this->propertyFactory->create($metadata, $propertyMetadata);
        }

        return $properties;
    }
}
