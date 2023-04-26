<?php

/*
 * @author       Kamil Smolak <kamil@smol.pl>
 * @link         http://www.smol.pl
 * @copyright    Copyright (c) 2022 Kamil Smolak
 */

namespace SmolCms\Bundle\ContentBlock\Type\Factory;

use SmolCms\Bundle\ContentBlock\Type\BlockTypeHandlerInterface;
use SmolCms\Bundle\ContentBlock\Type\PropertyTypeHandlerInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;

readonly class TypeHandlerFactory
{
    public function __construct(
        private ServiceLocator $blockHandlers,
        private ServiceLocator $propertyHandlers,
    ) {
    }

    public function createForBlock(string $name): BlockTypeHandlerInterface
    {
        if ($this->blockHandlers->has($name)) {
            return $this->blockHandlers->get($name);
        }

        $services = $this->blockHandlers->getProvidedServices();
        $serviceName = array_search($name, $services, true);
        if (!$serviceName || !$this->blockHandlers->has($serviceName)) {
            throw new \InvalidArgumentException(sprintf('Cannot create content block type "%s".', $name));
        }

        return $this->blockHandlers->get($serviceName);
    }

    public function createForProperty(string $name): PropertyTypeHandlerInterface
    {
        return $this->propertyHandlers->get($name);
    }
}
