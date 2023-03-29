<?php

/*
 * @author       Kamil Smolak <kamil@smol.pl>
 * @link         http://www.smol.pl
 * @copyright    Copyright (c) 2022 Kamil Smolak
 */

namespace SmolCms\Bundle\ContentBlock\Type\Factory;

use SmolCms\Bundle\ContentBlock\Type\HandlerInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;

class ContentBlockHandlerFactory
{
    public function __construct(
        private readonly ServiceLocator $handlers,
    ) {
    }

    public function create(string $type): HandlerInterface
    {
        if ($this->handlers->has($type)) {
            return $this->handlers->get($type);
        }

        $services = $this->handlers->getProvidedServices();
        $serviceName = array_search($type, $services, true);
        if (!$serviceName || !$this->handlers->has($serviceName)) {
            throw new \InvalidArgumentException(sprintf('Cannot create content block type "%s".', $type));
        }

        return $this->handlers->get($serviceName);
    }
}
