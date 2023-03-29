<?php

/*
 * @author       Kamil Smolak <kamil@smol.pl>
 * @link         http://www.smol.pl
 * @copyright    Copyright (c) 2022 Kamil Smolak
 */

namespace SmolCms\Bundle\ContentBlock\Metadata;

class ContentBlockRegistry
{
    private array $configByClass = [];
    public function __construct(
        readonly private array $config,
        readonly private array $providers,
    ) {
        foreach ($this->config as $item) {
            $this->configByClass[$item['class']] = $item;
        }
    }

    /**
     * @return array<string, BlockMetadata>
     */
    public function all(): iterable
    {
        foreach ($this->config as $name => $serviceConfig) {
            yield $name => $this->create($serviceConfig);
        }
    }

    public function has(string $name): bool
    {
        return isset($this->config[$name]) || isset($this->configByClass[$name]);
    }

    public function metadataFor(string $name): BlockMetadata
    {
        $serviceConfig = $this->config[$name] ?? $this->configByClass[$name] ?? null;

        if (!$serviceConfig) {
            throw new \InvalidArgumentException(sprintf(
                'Unknown content block "%s". The registered are: %s',
                $name,
                implode(', ', array_keys(array_merge($this->config, $this->configByClass)))
            ));
        }

        return $this->create($serviceConfig);
    }

    public function create(array $serviceConfig): BlockMetadata
    {
        return (new MetadataReader($serviceConfig['class']))->getMetadata();
    }

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
}
