<?php

/*
 * @author       Kamil Smolak <kamil@smol.pl>
 * @link         http://www.smol.pl
 * @copyright    Copyright (c) 2022 Kamil Smolak
 */

namespace SmolCms\Bundle\ContentBlock\Renderer;

use SmolCms\Bundle\ContentBlock\ContentBlock;
use SmolCms\Bundle\ContentBlock\ContentBlockFactory;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

readonly class ContentBlockRenderer
{
    public function __construct(
        private ContentBlockEngine $engine,
        private ContentBlockFactory $blockFactory,
        private DenormalizerInterface $denormalizer,
    ) {
    }

    public function render(mixed $data, string $theme): ?string
    {
        if (!$data) {
            return null;
        }

        $block = $this->resolveBlock($data);

        return $this->renderBlock($block, $theme);
    }

    public function renderBlocks(array $blocks, string $theme): ?string
    {
        if (!$blocks) {
            return null;
        }

        $html = '';

        foreach ($blocks as $block) {
            $block = $this->resolveBlock($block);
            $html .= $this->renderBlock($block, $theme);
        }

        return $html;
    }

    public function renderBlock(ContentBlock $block, string $theme): string
    {
        $object = $this->denormalizer->denormalize($block, $block->getMetadata()->class);

        return $this->engine->render($block->getMetadata(), $object, $theme);
    }

    private function resolveBlock(ContentBlock|array $block): ContentBlock
    {
        if ($block instanceof ContentBlock) {
            return $block;
        }

        return $this->blockFactory->create($block);
    }
}
