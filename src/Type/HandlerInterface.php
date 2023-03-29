<?php

/*
 * @author       Kamil Smolak <kamil@smol.pl>
 * @link         http://www.smol.pl
 * @copyright    Copyright (c) 2022 Kamil Smolak
 */

namespace SmolCms\Bundle\ContentBlock\Type;

use SmolCms\Bundle\ContentBlock\Metadata\BlockMetadata;
use SmolCms\Bundle\ContentBlock\ResolvedBlock;
use SmolCms\Bundle\ContentBlock\ResolvedProperty;
use Symfony\Component\Form\FormBuilderInterface;

interface HandlerInterface
{
    public function buildFormForBlock(ResolvedBlock $block, FormBuilderInterface $builder): void;
    public function buildFormForProperty(ResolvedProperty $property, FormBuilderInterface $builder): void;
    public function createView(object $object, BlockMetadata $metadata): object;
}
