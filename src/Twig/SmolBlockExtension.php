<?php

namespace SmolCms\Bundle\ContentBlock\Twig;

use SmolCms\Bundle\ContentBlock\Mapper\MapperInterface;
use SmolCms\Bundle\ContentBlock\Metadata\ContentBlockRegistry;
use SmolCms\Bundle\ContentBlock\Metadata\MetadataReader;
use SmolCms\Bundle\ContentBlock\Renderer\ContentBlockEngine;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class SmolBlockExtension extends AbstractExtension
{
    public function __construct(
        private readonly ContentBlockRegistry $registry,
        private readonly ContentBlockEngine $engine,
        private readonly MapperInterface $mapper,
    ) {
    }

    public function getFunctions(): iterable
    {
        yield new TwigFunction('smol_block_render', $this->render(...), ['is_safe' => ['html']]);
        yield new TwigFunction('smol_block_map', $this->map(...));
    }

    public function render(mixed $object, string $theme, ?string $mapTo = null): ?string
    {
        if ($mapTo) {
            $object = $this->map($object, $mapTo);
        }

        $reader = new MetadataReader(get_class($object));
        $blockMetadata = $reader->getMetadata();

        return $this->engine->render($blockMetadata, $object, $theme);
    }

    public function map(mixed $from, string $to): mixed
    {
        $metadata = $this->registry->metadataFor($to);

        if (is_a($from, $metadata->class, true)) {
            return $from;
        }

        return $this->mapper->map($from, $metadata->class);
    }
}
