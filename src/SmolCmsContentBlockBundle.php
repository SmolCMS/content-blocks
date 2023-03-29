<?php

/*
 * @author       Kamil Smolak <kamil@smol.pl>
 * @link         http://www.smol.pl
 * @copyright    Copyright (c) 2022 Kamil Smolak
 */

namespace SmolCms\Bundle\ContentBlock;

use SmolCms\Bundle\ContentBlock\DependencyInjection\Compiler\ContentBlockCompilerPass;
use SmolCms\Bundle\ContentBlock\DependencyInjection\SmolCmsExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class SmolCmsContentBlockBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new ContentBlockCompilerPass());
    }

    public function getContainerExtension(): SmolCmsExtension
    {
        return new SmolCmsExtension();
    }

    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
