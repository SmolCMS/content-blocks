<?php

/*
 * @author       Kamil Smolak <kamil@smol.pl>
 * @link         http://www.smol.pl
 * @copyright    Copyright (c) 2023 Kamil Smolak
 */

namespace SmolCms\Bundle\ContentBlock\Type;

use SmolCms\Bundle\ContentBlock\Form\Type\BlockCollectionType;
use SmolCms\Bundle\ContentBlock\Metadata\ContentBlockRegistry;
use SmolCms\Bundle\ContentBlock\ResolvedBlock;
use SmolCms\Bundle\ContentBlock\Type\Exception\UnexpectedTypeException;
use Symfony\Component\Form\FormBuilderInterface;

class GroupProvideHandler extends AbstractHandler
{
    public function __construct(
        private readonly ContentBlockRegistry $registry,
    ) {
    }

    /**
     * @throws UnexpectedTypeException
     */
    public function buildFormForBlock(ResolvedBlock $block, FormBuilderInterface $builder): void
    {
        $type = $block->getOuterProperty()?->getType();
        if (!$type instanceof GroupProvideType) {
            throw new Exception\UnexpectedTypeException($type, GroupProvideType::class);
        }

        $allowed = [];

        if ($type->allowed) {
            foreach ($type->allowed as $item) {
                $providers = $this->registry->providersFor($item);
                foreach ($providers as $provider) {
                    $allowed[] = $provider;
                }
            }
        }

        $builder->add('items', BlockCollectionType::class, [
            'label' => $type->label ?? null,
            'entry_options' => [
                'label' => false,
                'allowed' => $allowed,
            ],
            'button_add_options' => $type->formOptions['button_add_options'] ?? [],
        ]);
    }
}
