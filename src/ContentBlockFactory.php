<?php
/*
 * @author       Kamil Smolak <kamil@smol.pl>
 * @link         http://www.smol.pl
 * @copyright    Copyright (c) 2023 Kamil Smolak
 */

namespace SmolCms\Bundle\ContentBlock;

use SmolCms\Bundle\ContentBlock\Metadata\MetadataReaderException;
use SmolCms\Bundle\ContentBlock\Metadata\MetadataRegistry;

readonly class ContentBlockFactory
{
    public function __construct(
        private MetadataRegistry $registry,
    ) {
    }

    /**
     * @throws ContentBlockFactoryException
     * @throws MetadataReaderException
     */
    public function create(mixed $block, ?string $blockName = null, ?array $allowedBlocks = null): ContentBlock
    {
        if (!isset($block['name'], $block['data'])) {
            throw new ContentBlockFactoryException('Invalid content block.');
        }

        $blockName ??= $block['name'];

        if ($allowedBlocks === null) {
            $metadata = $this->registry->metadataFor($blockName);
        } else {
            $metadataAllowed = $this->registry->normalizeMetadata($allowedBlocks);
            if (!isset($metadataAllowed[$blockName])) {
                throw new \UnexpectedValueException(sprintf(
                    'Not allowed content block "%s". Allowed are: %s.',
                    $blockName,
                    implode(', ', array_keys($metadataAllowed))
                ));
            }

            $metadata = $metadataAllowed[$blockName];
        }

        return new ContentBlock($metadata, $block['data']);
    }
}
