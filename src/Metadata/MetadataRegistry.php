<?php

/*
 * @author       Kamil Smolak <kamil@smol.pl>
 * @link         http://www.smol.pl
 * @copyright    Copyright (c) 2022 Kamil Smolak
 */

namespace SmolCms\Bundle\ContentBlock\Metadata;

use SmolCms\Bundle\ContentBlock\Proxy\ContentBlockProxyInterface;

class MetadataRegistry
{
    private array $configByClass = [];
    public function __construct(
        private array $config,
        private array $providers,
    ) {
        foreach ($this->config as $item) {
            $this->configByClass[$item['class']] = $item;
        }
    }

    /**
     * @throws MetadataReaderException
     */
    public function registerBlock(string $class): void
    {
        $serviceConfig = ['class' => $class];
        $metadata = $this->readBlockMetadata($serviceConfig);

        $this->configByClass[$class] = $this->config[$metadata->name] = $serviceConfig;

        foreach ($metadata->provides as $item) {
            $this->providers[$item] = $class;
        }
    }

    /**
     * @return array<string, BlockMetadata>
     * @throws MetadataReaderException
     */
    public function all(): iterable
    {
        foreach ($this->config as $name => $serviceConfig) {
            yield $name => $this->readBlockMetadata($serviceConfig);
        }
    }

    public function has(string $name): bool
    {
        return isset($this->config[$name]) || isset($this->configByClass[$name]);
    }

    /**
     * @throws MetadataReaderException
     */
    public function metadataFor(string|object $nameOrObject): BlockMetadata
    {
        if (is_object($nameOrObject)) {
            if ($nameOrObject instanceof ContentBlockProxyInterface) {
                return $nameOrObject->__getMetadata();
            }

            $name = get_class($nameOrObject);
        } else {
            $name = $nameOrObject;
        }
        $serviceConfig = $this->config[$name] ?? $this->configByClass[$name] ?? null;

        if (!$serviceConfig) {
            throw new \InvalidArgumentException(sprintf(
                'Unknown content block "%s". The registered are: %s',
                $name,
                implode(', ', array_keys(array_merge($this->config, $this->configByClass)))
            ));
        }

        return $this->readBlockMetadata($serviceConfig);
    }

    /**
     * @throws MetadataReaderException
     */
    public function providersFor(string $name): iterable
    {
        $metadata = null;

        if ($this->has($name)) {
            $metadata = $this->metadataFor($name);
            yield $name => $metadata;
        }

        $providers = $this->providers[$name] ?? [];
        foreach ($providers as $providerName) {
            yield $providerName => $this->metadataFor($providerName);
        }

        if ($metadata) {
            $providers = $this->providers[$metadata->class] ?? [];
            foreach ($providers as $providerName) {
                yield $providerName => $this->metadataFor($providerName);
            }
        }
    }

    public function normalizeMetadata(array $allowedBlocks): array
    {
        $items = [];

        foreach ($allowedBlocks as $nameOrMetadata) {
            $metadata = $nameOrMetadata instanceof BlockMetadata ?
                $nameOrMetadata : $this->metadataFor($nameOrMetadata);
            $blockName = $metadata->name;

            if (isset($items[$blockName])) {
                throw new \LogicException(sprintf('Duplicated block name "%s".', $blockName));
            }

            $items[$blockName] = $metadata;
        }

        return $items;
    }

    /**
     * @throws MetadataReaderException
     */
    private function readBlockMetadata(array $serviceConfig): BlockMetadata
    {
        return (new BlockMetadataReader($serviceConfig['class']))->getMetadata();
    }
}
