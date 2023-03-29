<?php

/*
 * @author       Kamil Smolak <kamil@smol.pl>
 * @link         http://www.smol.pl
 * @copyright    Copyright (c) 2023 Kamil Smolak
 */

namespace SmolCms\Bundle\ContentBlock\Tests\Unit\Form\Type;

use SmolCms\Bundle\ContentBlock\ContentBlock\Builtin;
use SmolCms\Bundle\ContentBlock\ContentBlock\Group;
use SmolCms\Bundle\ContentBlock\Form\Type\BlockType;
use SmolCms\Bundle\ContentBlock\Form\Type\BlockWrapperType;
use SmolCms\Bundle\ContentBlock\Metadata\ContentBlockRegistry;
use SmolCms\Bundle\ContentBlock\ResolvedBlockFactory;
use SmolCms\Bundle\ContentBlock\ResolvedPropertyFactory;
use SmolCms\Bundle\ContentBlock\Tests\Fixtures\Block\OtherHandler;
use SmolCms\Bundle\ContentBlock\Tests\Fixtures\Block\Simple;
use SmolCms\Bundle\ContentBlock\Tests\Fixtures\Block\Simple2;
use SmolCms\Bundle\ContentBlock\Type\BuiltinHandler;
use SmolCms\Bundle\ContentBlock\Type\Factory\ContentBlockHandlerFactory;
use SmolCms\Bundle\ContentBlock\Type\GenericHandler;
use SmolCms\Bundle\ContentBlock\Type\GroupHandler;
use SmolCms\Bundle\ContentBlock\Type\GroupProvideHandler;
use SmolCms\Bundle\ContentBlock\Type\ProvideHandler;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class BlockWrapperTypeTest extends TypeTestCase
{
    private ContentBlockRegistry $registry;
    private ContentBlockHandlerFactory $handlerFactory;
    private ValidatorInterface $validator;
    private ResolvedPropertyFactory $propertyFactory;
    private ResolvedBlockFactory $blockFactory;

    protected function setUp(): void
    {
        $this->registry = new ContentBlockRegistry([
            'simple' => [
                'class' => Simple::class,
            ],
            'simple2' => [
                'class' => Simple2::class,
            ],
            'builtin' => [
                'class' => Builtin::class,
            ],
            'group' => [
                'class' => Group::class,
            ],
        ], []);


        $this->handlerFactory = new ContentBlockHandlerFactory(new ServiceLocator([
            GenericHandler::class => fn () => new GenericHandler($this->handlerFactory),
            BuiltinHandler::class => fn () => new BuiltinHandler(),
            OtherHandler::class => fn () => new OtherHandler(),
            GroupHandler::class => fn () => new GroupHandler(),
            ProvideHandler::class => fn () => new ProvideHandler($this->registry),
            GroupProvideHandler::class => fn () => new GroupProvideHandler($this->registry),
        ]));
        $this->validator = Validation::createValidator();
        $this->propertyFactory = new ResolvedPropertyFactory($this->registry, $this->validator);
        $this->blockFactory = new ResolvedBlockFactory($this->propertyFactory);

        parent::setUp();
    }

    protected function getExtensions(): array
    {
        return [
            new PreloadedExtension([
                new BlockWrapperType($this->registry),
                new BlockType($this->handlerFactory, $this->blockFactory),
            ], []),
            new ValidatorExtension($this->validator),
        ];
    }

    /**
     * @dataProvider getValidData
     */
    public function testSubmitValidData(array $formData, array $exceptedData): void
    {
        $form = $this->factory->create(BlockWrapperType::class);
        $form->submit($formData);

        $this->assertTrue($form->isSubmitted());
        $this->assertTrue($form->isSynchronized());
        $message = '';
        foreach ($form->getErrors(true) as $error) {
            $message .= sprintf('%s - %s', $error->getCause()->getPropertyPath(), $error->getMessage()) . PHP_EOL;
        }
        $this->assertTrue($form->isValid(), $message);
        $this->assertEquals($exceptedData, $form->getData());
    }

    public function getValidData(): iterable
    {
        yield 'simple' => [
            [
                'name' => 'simple',
                'properties' => [
                    'string' => 'bar',
                    'stringNullable' => null,
                    'handlerOnProperty' => [
                        'other' => 'other',
                    ],
                    'handlerOnProperty2' => [
                        'other' => 'other',
                    ],
                    'innerSimple' => [
                        'string' => 'baz',
                        'stringNullable' => null,
                    ],
                    'group' => [
                        'items' => [
                            0 => [
                                'name' => 'simple2',
                                'properties' => [
                                    'string' => 'foo',
                                    'stringNullable' => null,
                                ],
                            ],
                            1 => [
                                'name' => 'simple2',
                                'properties' => [
                                    'string' => 'bar',
                                    'stringNullable' => null,
                                ],
                            ],
                            2 => [
                                'name' => 'simple2',
                                'properties' => [
                                    'string' => 'baz',
                                    'stringNullable' => null,
                                ],
                            ],
                        ],
                    ],
                    'groupArray' => [
                        'items' => [
                            0 => [
                                'name' => 'simple2',
                                'properties' => [
                                    'string' => 'foo',
                                    'stringNullable' => null,
                                ],
                            ],
                            1 => [
                                'name' => 'simple2',
                                'properties' => [
                                    'string' => 'bar',
                                    'stringNullable' => null,
                                ],
                            ],
                            2 => [
                                'name' => 'simple2',
                                'properties' => [
                                    'string' => 'baz',
                                    'stringNullable' => null,
                                ],
                            ],
                        ],
                    ],
                    'groupProvide' => [
                        'items' => [
                            0 => [
                                'properties' => [
                                    'string' => 'foo',
                                    'stringNullable' => null,
                                ],
                            ],
                        ],
                    ],
                    'provideString' => [
                        'properties' => [
                            'value' => 'provideStringVal',
                        ],
                    ],
                    'formOnProperty' => 'formOnPropertyVal',
                ],
            ],
            [
                'name' => 'simple',
                'properties' => [
                    'string' => [
                        'name' => 'builtin',
                        'properties' => [
                            'value' => 'bar',
                        ],
                    ],
                    'stringNullable' => [
                        'name' => 'builtin',
                        'properties' => [
                            'value' => null,
                        ],
                    ],
                    'handlerOnProperty' => [
                        'name' => 'builtin',
                        'properties' => [
                            'other' => 'other',
                        ],
                    ],
                    'handlerOnProperty2' => [
                        'name' => 'simple2',
                        'properties' => [
                            'other' => 'other',
                        ],
                    ],
                    'innerSimple' => [
                        'name' => 'simple2',
                        'properties' => [
                            'string' => [
                                'name' => 'builtin',
                                'properties' => [
                                    'value' => 'baz',
                                ],
                            ],
                            'stringNullable' => [
                                'name' => 'builtin',
                                'properties' => [
                                    'value' => null,
                                ],
                            ],
                        ],
                    ],
                    'group' => [
                        'name' => 'group',
                        'properties' => [
                            'items' => [
                                0 => [
                                    'name' => 'simple2',
                                    'properties' => [
                                        'string' => [
                                            'name' => 'builtin',
                                            'properties' => [
                                                'value' => 'foo',
                                            ],
                                        ],
                                        'stringNullable' => [
                                            'name' => 'builtin',
                                            'properties' => [
                                                'value' => null,
                                            ],
                                        ],
                                    ],
                                ],
                                1 => [
                                    'name' => 'simple2',
                                    'properties' => [
                                        'string' => [
                                            'name' => 'builtin',
                                            'properties' => [
                                                'value' => 'bar',
                                            ],
                                        ],
                                        'stringNullable' => [
                                            'name' => 'builtin',
                                            'properties' => [
                                                'value' => null,
                                            ],
                                        ],
                                    ],
                                ],
                                2 => [
                                    'name' => 'simple2',
                                    'properties' => [
                                        'string' => [
                                            'name' => 'builtin',
                                            'properties' => [
                                                'value' => 'baz',
                                            ],
                                        ],
                                        'stringNullable' => [
                                            'name' => 'builtin',
                                            'properties' => [
                                                'value' => null,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'groupArray' => [
                        'name' => 'group',
                        'properties' => [
                            'items' => [
                                0 => [
                                    'name' => 'simple2',
                                    'properties' => [
                                        'string' => [
                                            'name' => 'builtin',
                                            'properties' => [
                                                'value' => 'foo',
                                            ],
                                        ],
                                        'stringNullable' => [
                                            'name' => 'builtin',
                                            'properties' => [
                                                'value' => null,
                                            ],
                                        ],
                                    ],
                                ],
                                1 => [
                                    'name' => 'simple2',
                                    'properties' => [
                                        'string' => [
                                            'name' => 'builtin',
                                            'properties' => [
                                                'value' => 'bar',
                                            ],
                                        ],
                                        'stringNullable' => [
                                            'name' => 'builtin',
                                            'properties' => [
                                                'value' => null,
                                            ],
                                        ],
                                    ],
                                ],
                                2 => [
                                    'name' => 'simple2',
                                    'properties' => [
                                        'string' => [
                                            'name' => 'builtin',
                                            'properties' => [
                                                'value' => 'baz',
                                            ],
                                        ],
                                        'stringNullable' => [
                                            'name' => 'builtin',
                                            'properties' => [
                                                'value' => null,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'groupProvide' => [
                        'name' => 'group',
                        'properties' => [
                            'items' => [
                                0 => [
                                    'name' => 'simple2',
                                    'properties' => [
                                        'string' => [
                                            'name' => 'builtin',
                                            'properties' => [
                                                'value' => 'foo',
                                            ],
                                        ],
                                        'stringNullable' => [
                                            'name' => 'builtin',
                                            'properties' => [
                                                'value' => null,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'provideString' => [
                        'name' => 'builtin',
                        'properties' => [
                            'value' => 'provideStringVal',
                        ],
                    ],
                    'formOnProperty' => [
                        'name' => 'simple2',
                        'properties' => [
                            'value' => 'formOnPropertyVal',
                        ],
                    ],
                ],
            ],
        ];

        yield 'simple2' => [
            [
                'name' => 'simple2',
                'properties' => [
                    'string' => 'bar',
                    'stringNullable' => null,
                ],
            ],
            [
                'name' => 'simple2',
                'properties' => [
                    'string' => [
                        'name' => 'builtin',
                        'properties' => [
                            'value' => 'bar',
                        ],
                    ],
                    'stringNullable' => [
                        'name' => 'builtin',
                        'properties' => [
                            'value' => null,
                        ],
                    ],
                ],
            ],
        ];
    }
}
