<?php

/*
 * @author       Kamil Smolak <kamil@smol.pl>
 * @link         http://www.smol.pl
 * @copyright    Copyright (c) 2023 Kamil Smolak
 */

namespace SmolCms\Bundle\ContentBlock\Type;

use SmolCms\Bundle\ContentBlock\Form\Type\BlockCollectionType;
use SmolCms\Bundle\ContentBlock\ResolvedBlock;
use SmolCms\Bundle\ContentBlock\Type\Exception\UnexpectedTypeException;
use Symfony\Component\Form\FormBuilderInterface;

class GroupHandler extends AbstractHandler
{
    /**
     * @throws UnexpectedTypeException
     */
    public function buildFormForBlock(ResolvedBlock $block, FormBuilderInterface $builder): void
    {
        $type = $block->getOuterProperty()?->getType();
        if (!$type instanceof GroupType) {
            throw new Exception\UnexpectedTypeException($type, GroupType::class);
        }

        $builder->add('items', BlockCollectionType::class, [
            'entry_options' => [
                'label' => false,
                'allowed' => $type->allowed,
            ],
            'button_add_options' => $type->formOptions['button_add_options'] ?? [],
        ]);
    }
}
