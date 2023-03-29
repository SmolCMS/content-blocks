<?php

/*
 * @author       Kamil Smolak <kamil@smol.pl>
 * @link         http://www.smol.pl
 * @copyright    Copyright (c) 2023 Kamil Smolak
 */

namespace SmolCms\Bundle\ContentBlock\Tests\Fixtures\Block;

use SmolCms\Bundle\ContentBlock\ResolvedBlock;
use SmolCms\Bundle\ContentBlock\Type\AbstractHandler;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OtherHandler extends AbstractHandler
{
    public function buildFormForBlock(ResolvedBlock $block, FormBuilderInterface $builder): void
    {
        $builder->add('other', TextType::class);
    }
}
