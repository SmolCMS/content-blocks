<?php

namespace SmolCms\Bundle\ContentBlock\Type;

use SmolCms\Bundle\ContentBlock\ResolvedBlock;
use Symfony\Component\Form\FormBuilderInterface;

interface BlockTypeHandlerInterface extends TypeHandlerInterface
{
    public function buildFormForBlock(ResolvedBlock $block, FormBuilderInterface $builder): void;
}
