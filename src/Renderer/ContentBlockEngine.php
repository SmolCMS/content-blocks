<?php

namespace SmolCms\Bundle\ContentBlock\Renderer;

use SmolCms\Bundle\ContentBlock\Metadata\BlockMetadata;
use Twig\Environment;
use Twig\TemplateWrapper;

class ContentBlockEngine
{
    private array $templates = [];

    public function __construct(
        private readonly Environment $twig,
    ) {
    }

    public function render(BlockMetadata $blockMetadata, mixed $object, string $theme): string
    {
        return $this->getTemplate($theme)->renderBlock($blockMetadata->name, [
            'object' => $object,
            'theme' => $theme,
        ]);
    }

    private function getTemplate(string $theme): TemplateWrapper
    {
        $this->templates[$theme] ??= $this->twig->load($theme);

        return $this->templates[$theme];
    }
}
