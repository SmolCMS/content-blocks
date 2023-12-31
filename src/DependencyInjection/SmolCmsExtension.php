<?php

/*
 * @author       Kamil Smolak <kamil@smol.pl>
 * @link         http://www.smol.pl
 * @copyright    Copyright (c) 2022 Kamil Smolak
 */

namespace SmolCms\Bundle\ContentBlock\DependencyInjection;

use SmolCms\Bundle\ContentBlock\Attribute\AsContentBlock;
use SmolCms\Bundle\ContentBlock\Mapper\MapperInterface;
use SmolCms\Bundle\ContentBlock\Type\BlockTypeHandlerInterface;
use SmolCms\Bundle\ContentBlock\Type\Factory\TypeHandlerFactory;
use SmolCms\Bundle\ContentBlock\Metadata\MetadataRegistry;
use SmolCms\Bundle\ContentBlock\Type\PropertyTypeHandlerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Argument\ServiceLocatorArgument;
use Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class SmolCmsExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../../config'));
        $loader->load('services.php');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->registerAttributeForAutoconfiguration(
            AsContentBlock::class,
            static function (ChildDefinition $definition, AsContentBlock $attribute): void {
                $definition->addTag('smol_cms.content_blocks', $attribute->serviceConfig());

                foreach ($attribute->provides as $provide) {
                    $definition->addTag('smol_cms.content_block_providers', ['provide' => $provide]);
                }
            }
        );

        $container->register(MetadataRegistry::class)
            ->setArguments([
                new ServiceLocatorArgument(new TaggedIteratorArgument('smol_cms.content_blocks', 'key', null, true)),
            ])
        ;

        $container->registerForAutoconfiguration(BlockTypeHandlerInterface::class)
            ->addTag('smol_content_block.block_type_handlers');

        $container->registerForAutoconfiguration(PropertyTypeHandlerInterface::class)
            ->addTag('smol_content_block.property_type_handlers');

        $container->register(TypeHandlerFactory::class)
            ->setArguments([
                new ServiceLocatorArgument(new TaggedIteratorArgument('smol_content_block.block_type_handlers', null, null, true)),
                new ServiceLocatorArgument(new TaggedIteratorArgument('smol_content_block.property_type_handlers', null, null, true)),
            ])
        ;

        $container->registerForAutoconfiguration(MapperInterface::class)
            ->addTag('smol_cms.content_block_mapper');
    }

    public function prepend(ContainerBuilder $container): void
    {
        $container->prependExtensionConfig('twig', [
            'form_themes' => [
                '@SmolCmsContentBlock/Form/form_content_blocks.html.twig',
            ],
        ]);
    }
}
