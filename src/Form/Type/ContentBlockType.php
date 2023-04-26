<?php

/*
 * @author       Kamil Smolak <kamil@smol.pl>
 * @link         http://www.smol.pl
 * @copyright    Copyright (c) 2022 Kamil Smolak
 */

namespace SmolCms\Bundle\ContentBlock\Form\Type;

use SmolCms\Bundle\ContentBlock\Metadata\BlockMetadata;
use SmolCms\Bundle\ContentBlock\Metadata\MetadataRegistry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContentBlockType extends AbstractType
{
    public function __construct(
        private readonly MetadataRegistry $registry,
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('data_class');

        $resolver->setDefault('block_metadata', null);
        $resolver->setAllowedTypes('block_metadata', [BlockMetadata::class, 'null']);
        $resolver->setNormalizer('block_metadata', function (Options $options, ?BlockMetadata $metadata) {
            if ($metadata) {
                return $metadata;
            }

            return $this->registry->metadataFor($options['data_class']);
        });
    }

    public function getParent(): string
    {
        return ContentBlockDataType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'smol_content_block';
    }
}
