<?php

/*
 * @author       Kamil Smolak <kamil@smol.pl>
 * @link         http://www.smol.pl
 * @copyright    Copyright (c) 2023 Kamil Smolak
 */

namespace SmolCms\Bundle\ContentBlock;

use SmolCms\Bundle\ContentBlock\Form\Type\ContentBlockWrapperType;
use SmolCms\Bundle\ContentBlock\Form\Type\ContentBlockDataType;
use SmolCms\Bundle\ContentBlock\Mapper\CompositeMapper;
use SmolCms\Bundle\ContentBlock\Renderer\ContentBlockRenderer;
use SmolCms\Bundle\ContentBlock\Serializer\ContentBlockDenormalizer;
use SmolCms\Bundle\ContentBlock\Twig\SmolBlockExtension;
use SmolCms\Bundle\ContentBlock\Type\BuiltinTypeHandler;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return static function (ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services()
        ->defaults()
            ->autowire()
            ->autoconfigure()
    ;

    $services->load('SmolCms\\Bundle\\ContentBlock\\', '../src')
        ->exclude([
            '../src/DependencyInjection/',
            '../src/SmolCmsContentBlockBundle.php',
        ]);

    $services->set(BuiltinTypeHandler::class)
        ->arg('$formTypeGuesser', service('form.type_guesser.validator'));

    $services->set(CompositeMapper::class)
        ->autoconfigure(false)
        ->autowire(false)
    ;

    $services->set('smol_content_blocks.mappers', CompositeMapper::class)
        ->arg('$mappers', tagged_iterator('smol_cms.content_block_mapper'))
        ->autoconfigure(false)
        ->autowire(false)
    ;

	$services->set(ContentBlockRenderer::class)
		->arg('$denormalizer', service(ContentBlockDenormalizer::class))
	;

    $services->set(ContentBlockDenormalizer::class)
        ->arg('$mapper', service('smol_content_blocks.mappers'))
    ;

    $services->set(SmolBlockExtension::class)
        ->arg('$mapper', service('smol_content_blocks.mappers'))
    ;

    $services->set(ContentBlockWrapperType::class)
        ->arg('$mapper', service('smol_content_blocks.mappers'))
    ;

    $services->set(ContentBlockDataType::class)
        ->arg('$mapper', service('smol_content_blocks.mappers'))
    ;
};
