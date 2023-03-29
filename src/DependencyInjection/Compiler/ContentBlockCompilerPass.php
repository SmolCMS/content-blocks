<?php

namespace SmolCms\Bundle\ContentBlock\DependencyInjection\Compiler;

use LogicException;
use SmolCms\Bundle\ContentBlock\Metadata\ContentBlockRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ContentBlockCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has(ContentBlockRegistry::class)) {
            return;
        }

        $config = $this->getConfig($container);
        $providers = $this->getProviders($container);

        $container->findDefinition(ContentBlockRegistry::class)
            ->setArgument('$config', $config)
            ->setArgument('$providers', $providers)
        ;
    }

    public function getConfig(ContainerBuilder $container): array
    {
        $config = [];

        foreach ($container->findTaggedServiceIds('smol_cms.content_blocks') as $id => $tags) {
            $definition = $container->findDefinition($id);
            $definition->setShared(false);

            foreach ($tags as $tag) {
                if (!array_key_exists('key', $tag)) {
                    throw new LogicException(
                        sprintf('"%s" tag for service "%s" requires a "key" attribute.', 'smol_cms.content_blocks', $id)
                    );
                }

                if (isset($config[$tag['key']])) {
                    throw new LogicException(
                        sprintf('Cannot register "%s" content block, because "%s" key is already registered for service "%s".', $id, $tag['key'], $config[$tag['key']]['service_id'])
                    );
                }

                $tag['class'] = $definition->getClass();
                $tag['service_id'] = $id;
                $config[$tag['key']] = $tag;
            }
        }

        return $config;
    }

    public function getProviders(ContainerBuilder $container): array
    {
        $providers = [];

        foreach ($container->findTaggedServiceIds('smol_cms.content_block_providers') as $id => $tags) {
            $definition = $container->findDefinition($id);
            $definition->setShared(false);

            foreach ($tags as $tag) {
                if (!array_key_exists('provide', $tag)) {
                    throw new LogicException(
                        sprintf('"%s" tag for service "%s" requires a "provide" attribute.', 'smol_cms.content_block_providers', $id)
                    );
                }

                $providers[$tag['provide']][] = $definition->getClass();
            }
        }

        return $providers;
    }
}
