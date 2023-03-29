<?php

namespace SmolCms\Bundle\ContentBlock\Renderer;

use SmolCms\Bundle\ContentBlock\Metadata\BlockMetadata;
use SmolCms\Bundle\ContentBlock\Type\Factory\ContentBlockHandlerFactory;
use SmolCms\Bundle\ContentBlock\Type\GenericHandler;
use Twig\Environment;
use Twig\TemplateWrapper;

class ContentBlockEngine
{
    private array $templates = [];

    public function __construct(
        private readonly Environment $twig,
        private readonly ContentBlockHandlerFactory $typeFactory,
    ) {
    }

    public function render(BlockMetadata $blockMetadata, mixed $object, string $theme): string
    {
        $type = $this->typeFactory->create($blockMetadata->type->getHandler());
        $viewObject = $type->createView($object, $blockMetadata);

        return $this->getTemplate($theme)->renderBlock($blockMetadata->name, [
            'object' => $viewObject,
            'theme' => $theme,
        ]);
    }

    private function getTemplate(string $theme): TemplateWrapper
    {
        $this->templates[$theme] ??= $this->twig->load($theme);

        return $this->templates[$theme];
    }
}
