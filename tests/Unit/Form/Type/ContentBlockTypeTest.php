<?php

/*
 * @author       Kamil Smolak <kamil@smol.pl>
 * @link         http://www.smol.pl
 * @copyright    Copyright (c) 2023 Kamil Smolak
 */

namespace SmolCms\Bundle\ContentBlock\Tests\Unit\Form\Type;

use SmolCms\Bundle\ContentBlock\EventListener\FormErrorMapperExtension;
use SmolCms\Bundle\ContentBlock\Form\Type\ContentBlockWrapperType;
use SmolCms\Bundle\ContentBlock\Form\Type\ContentBlockDataType;
use SmolCms\Bundle\ContentBlock\Form\Type\ContentBlockType;
use SmolCms\Bundle\ContentBlock\Mapper\CompositeMapper;
use SmolCms\Bundle\ContentBlock\Mapper\FromBuiltinMapper;
use SmolCms\Bundle\ContentBlock\Mapper\MapperException;
use SmolCms\Bundle\ContentBlock\Mapper\MapperInterface;
use SmolCms\Bundle\ContentBlock\Metadata\BlockMetadata;
use SmolCms\Bundle\ContentBlock\Metadata\MetadataRegistry;
use SmolCms\Bundle\ContentBlock\ResolvedBlockFactory;
use SmolCms\Bundle\ContentBlock\ResolvedPropertyFactory;
use SmolCms\Bundle\ContentBlock\Tests\Fixtures\Block\TestBlockInterface;
use SmolCms\Bundle\ContentBlock\Tests\Fixtures\Block\TestBlockInterface1;
use SmolCms\Bundle\ContentBlock\Tests\Fixtures\Block\TestBlockInterface2;
use SmolCms\Bundle\ContentBlock\Tests\Fixtures\Block\TestBuiltin;
use SmolCms\Bundle\ContentBlock\Tests\Fixtures\Block\TestCustomTypeHandler;
use SmolCms\Bundle\ContentBlock\Tests\Fixtures\Block\TestDefault;
use SmolCms\Bundle\ContentBlock\Tests\Fixtures\Block\TestGetSetIs;
use SmolCms\Bundle\ContentBlock\Tests\Fixtures\Block\TestGroup;
use SmolCms\Bundle\ContentBlock\Tests\Fixtures\Block\TestGroupAllowed;
use SmolCms\Bundle\ContentBlock\Tests\Fixtures\Block\TestGroupAllowedCustom;
use SmolCms\Bundle\ContentBlock\Tests\Fixtures\Block\TestGroupProvide;
use SmolCms\Bundle\ContentBlock\Tests\Fixtures\Block\TestInvalidMappingStrategy;
use SmolCms\Bundle\ContentBlock\Tests\Fixtures\Block\TestMixed;
use SmolCms\Bundle\ContentBlock\Tests\Fixtures\Block\TestNotRegistered;
use SmolCms\Bundle\ContentBlock\Tests\Fixtures\Block\TestObject;
use SmolCms\Bundle\ContentBlock\Tests\Fixtures\Block\TestProvide;
use SmolCms\Bundle\ContentBlock\Tests\Fixtures\Block\TestProvideAllowChange;
use SmolCms\Bundle\ContentBlock\Tests\Fixtures\Block\TestProxy;
use SmolCms\Bundle\ContentBlock\Tests\Fixtures\Block\TestReadonly;
use SmolCms\Bundle\ContentBlock\Tests\Fixtures\Block\TestSimple;
use SmolCms\Bundle\ContentBlock\Tests\Fixtures\Block\TestSimple2;
use SmolCms\Bundle\ContentBlock\Tests\Fixtures\Block\TestUninitialized;
use SmolCms\Bundle\ContentBlock\Tests\Fixtures\Block\TestValidation;
use SmolCms\Bundle\ContentBlock\Tests\Fixtures\Block\TestValidationInner;
use SmolCms\Bundle\ContentBlock\Tests\Fixtures\Block\TestValidationProvideGroup;
use SmolCms\Bundle\ContentBlock\Tests\Fixtures\Block\TestValidationProvideGroupInner;
use SmolCms\Bundle\ContentBlock\Tests\Fixtures\Block\TestValidationProvideGroupInner2;
use SmolCms\Bundle\ContentBlock\Type\Builtin;
use SmolCms\Bundle\ContentBlock\Type\BuiltinTypeHandler;
use SmolCms\Bundle\ContentBlock\Type\Factory\TypeHandlerFactory;
use SmolCms\Bundle\ContentBlock\Type\GenericType;
use SmolCms\Bundle\ContentBlock\Type\GenericTypeHandler;
use SmolCms\Bundle\ContentBlock\Type\Group;
use SmolCms\Bundle\ContentBlock\Type\GroupTypeHandler;
use SmolCms\Bundle\ContentBlock\Type\ProvideTypeHandler;
use SmolCms\Bundle\ContentBlock\Type\UseFormTypeHandler;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormTypeGuesserChain;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ContentBlockTypeTest extends TypeTestCase
{
    private MetadataRegistry $registry;
    private TypeHandlerFactory $handlerFactory;
    private ValidatorInterface $validator;
    private ResolvedPropertyFactory $propertyFactory;
    private ResolvedBlockFactory $blockFactory;
    private MapperInterface $mapper;

    protected function setUp(): void
    {
        $this->registry = new MetadataRegistry([
            'builtin' => [
                'class' => Builtin::class,
            ],
            'group' => [
                'class' => Group::class,
            ],
            'test_simple' => [
                'class' => TestSimple::class,
            ],
            'test_simple2' => [
                'class' => TestSimple2::class,
            ],
            'test_group' => [
                'class' => TestGroup::class,
            ],
            'test_group_allowed' => [
                'class' => TestGroupAllowed::class,
            ],
            'test_group_allowed_custom' => [
                'class' => TestGroupAllowedCustom::class,
            ],
            'test_group_provide' => [
                'class' => TestGroupProvide::class,
            ],
            'test_builtin' => [
                'class' => TestBuiltin::class,
            ],
            'test_mixed' => [
                'class' => TestMixed::class,
            ],
            'test_proxy' => [
                'class' => TestProxy::class,
            ],
            'test_provide' => [
                'class' => TestProvide::class,
            ],
            'test_object' => [
                'class' => TestObject::class,
            ],
            'test_uninitialized' => [
                'class' => TestUninitialized::class,
            ],
            'test_default' => [
                'class' => TestDefault::class,
            ],
            'test_getsetis' => [
                'class' => TestGetSetIs::class,
            ],
            'test_readonly' => [
                'class' => TestReadonly::class,
            ],
            'test_interface1' => [
                'class' => TestBlockInterface1::class,
            ],
            'test_interface2' => [
                'class' => TestBlockInterface2::class,
            ],
            'test_invalid_mapping_strategy' => [
                'class' => TestInvalidMappingStrategy::class,
            ],
        ], [
            TestBlockInterface::class => [
                TestBlockInterface1::class,
                TestBlockInterface2::class,
            ],
        ]);

        $typeHandlerLocator = new ServiceLocator([
            GenericTypeHandler::class => fn() => new GenericTypeHandler($this->handlerFactory),
            BuiltinTypeHandler::class => fn() => new BuiltinTypeHandler(new FormTypeGuesserChain([])),
            ProvideTypeHandler::class => fn() => new ProvideTypeHandler($this->registry),
            GroupTypeHandler::class => fn() => new GroupTypeHandler($this->registry),
            UseFormTypeHandler::class => fn() => new UseFormTypeHandler(),
            TestCustomTypeHandler::class => fn() => new TestCustomTypeHandler(),
        ]);
        $this->handlerFactory = new TypeHandlerFactory($typeHandlerLocator, $typeHandlerLocator);
        $this->validator = Validation::createValidatorBuilder()
            ->enableAnnotationMapping()
            ->getValidator();
        $this->propertyFactory = new ResolvedPropertyFactory($this->registry, $this->validator);
        $this->blockFactory = new ResolvedBlockFactory($this->propertyFactory);
        $this->mapper = new CompositeMapper([
            new FromBuiltinMapper(),
        ]);

        parent::setUp();
    }

    protected function getExtensions(): array
    {
        return [
            new PreloadedExtension([
                new ContentBlockType($this->registry),
                new ContentBlockDataType($this->handlerFactory, $this->blockFactory, $this->mapper),
                new ContentBlockWrapperType($this->registry, $this->mapper),
            ], []),
            new ValidatorExtension($this->validator),
            new FormErrorMapperExtension(),
        ];
    }

    /**
     * @dataProvider getValidData
     */
    public function testSubmitValidData(array $formData, mixed $exceptedData): void
    {
        $form = $this->factory->create(ContentBlockType::class, null, [
            'data_class' => get_class($exceptedData),
        ]);
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
        yield 'test_builtin' => [
            [
                'string' => 'foo',
                'int' => '123',
                'float' => '123.321',
                'bool' => '1',
            ],
            (static function () {
                $item = new TestBuiltin();
                $item->string = 'foo';
                $item->int = 123;
                $item->float = 123.321;
                $item->bool = true;

                return $item;
            })(),
        ];

        yield 'test_mixed' => [
            [
                'emptyType' => 'foo',
                'mixedType' => 'bar',
                'unionType' => 'baz',
            ],
            (static function () {
                $item = new TestMixed();
                $item->emptyType = 'foo';
                $item->mixedType = 'bar';
                $item->unionType = 'baz';

                return $item;
            })(),
        ];

        yield 'test_simple2' => [
            [
                'string' => 'foo',
                'stringNullable' => null,
            ],
            $this->createSimple2('foo'),
        ];

        yield 'test_simple' => [
            [
                'string' => 'bar',
                'stringNullable' => null,
                'innerSimple' => [
                    'string' => 'baz',
                    'stringNullable' => null,
                ],
                'formOnProperty' => 'formOnPropertyVal',
            ],
            (static function () {
                $item = new TestSimple();
                $item->string = 'bar';
                $item->innerSimple = new TestSimple2();
                $item->innerSimple->string = 'baz';
                $item->formOnProperty = 'formOnPropertyVal';

                return $item;
            })(),
        ];

        yield 'test_group_provide' => [
            [
                'property' => [
                    [
                        'name' => 'test_simple2',
                        'data' => [
                            'string' => 'foo',
                            'stringNullable' => null,
                        ],
                    ],
                    [
                        'name' => 'test_simple2',
                        'data' => [
                            'string' => 'bar',
                            'stringNullable' => null,
                        ],
                    ],
                ],
                'customType' => [
                    [
                        'name' => 'test_simple2',
                        'data' => [
                            'string' => 'foo',
                            'stringNullable' => null,
                        ],
                    ],
                    [
                        'name' => 'test_simple2',
                        'data' => [
                            'string' => 'bar',
                            'stringNullable' => null,
                        ],
                    ],
                ],
            ],
            (function () {
                $item = new TestGroupProvide();
                $item->property = [
                    $this->createSimple2('foo'),
                    $this->createSimple2('bar'),
                ];
                $item->customType = [
                    $this->createSimple2('foo'),
                    $this->createSimple2('bar'),
                ];

                return $item;
            })(),
        ];

        yield 'test_provide' => [
            [
                'string' => [
                    'data' => [
                        'value' => 'foo',
                    ],
                ],
                'compound' => [
                    'name' => 'test_simple2',
                    'data' => [
                        'string' => 'bar',
                        'stringNullable' => null,
                    ],
                ],
                'emptyTypeAllowed' => [
                    'name' => 'test_simple2',
                    'data' => [
                        'string' => 'baz',
                        'stringNullable' => null,
                    ],
                ],
            ],
            (function () {
                $item = new TestProvide();
                $item->string = 'foo';
                $item->compound = $this->createSimple2('bar');
                $item->emptyTypeAllowed = $this->createSimple2('baz');

                return $item;
            })(),
        ];

        yield 'test_group' => [
            [
                'array' => [
                    0 => [
                        'name' => 'test_simple2',
                        'data' => [
                            'string' => 'foo1',
                            'stringNullable' => null,
                        ],
                    ],
                    1 => [
                        'name' => 'test_simple2',
                        'data' => [
                            'string' => 'bar2',
                            'stringNullable' => null,
                        ],
                    ],
                    2 => [
                        'name' => 'test_simple2',
                        'data' => [
                            'string' => 'baz3',
                            'stringNullable' => null,
                        ],
                    ],
                ],
                'iterable' => [
                    0 => [
                        'name' => 'test_simple2',
                        'data' => [
                            'string' => 'foo1',
                            'stringNullable' => null,
                        ],
                    ],
                    1 => [
                        'name' => 'test_simple2',
                        'data' => [
                            'string' => 'bar2',
                            'stringNullable' => null,
                        ],
                    ],
                    2 => [
                        'name' => 'test_simple2',
                        'data' => [
                            'string' => 'baz3',
                            'stringNullable' => null,
                        ],
                    ],
                ],
                'group' => [
                    0 => [
                        'name' => 'test_simple2',
                        'data' => [
                            'string' => 'foo1',
                            'stringNullable' => null,
                        ],
                    ],
                    1 => [
                        'name' => 'test_simple2',
                        'data' => [
                            'string' => 'bar2',
                            'stringNullable' => null,
                        ],
                    ],
                    2 => [
                        'name' => 'test_simple2',
                        'data' => [
                            'string' => 'baz3',
                            'stringNullable' => null,
                        ],
                    ],
                ],
            ],
            (function () {
                $item = new TestGroup();
                $item->array = [
                    $this->createSimple2('foo1'),
                    $this->createSimple2('bar2'),
                    $this->createSimple2('baz3'),
                ];
                $item->iterable = [
                    $this->createSimple2('foo1'),
                    $this->createSimple2('bar2'),
                    $this->createSimple2('baz3'),
                ];
                $item->group = new Group();
                $item->group->items = [
                    $this->createSimple2('foo1'),
                    $this->createSimple2('bar2'),
                    $this->createSimple2('baz3'),
                ];

                return $item;
            })(),
        ];

        yield 'test_group_skip_empty' => [
            [
                'array' => [
                    0 => [
                        'name' => 'test_simple2',
                        'data' => [
                            'string' => 'foo1',
                            'stringNullable' => null,
                        ],
                    ],
                    1 => [
                        'name' => null,
                    ],
                ],
                'iterable' => [
                    0 => [
                        'name' => 'test_simple2',
                        'data' => [
                            'string' => 'foo1',
                            'stringNullable' => null,
                        ],
                    ],
                    1 => [
                        'name' => null,
                    ],
                ],
                'group' => [
                    0 => [
                        'name' => 'test_simple2',
                        'data' => [
                            'string' => 'foo1',
                            'stringNullable' => null,
                        ],
                    ],
                    1 => [
                        'name' => null,
                    ],
                ],
            ],
            (function () {
                $item = new TestGroup();
                $item->array = [
                    $this->createSimple2('foo1'),
                ];
                $item->iterable = [
                    $this->createSimple2('foo1'),
                ];
                $item->group = new Group();
                $item->group->items = [
                    $this->createSimple2('foo1'),
                ];

                return $item;
            })(),
        ];

        yield 'test_group_allowed' => [
            [
                'items' => [
                    0 => [
                        'name' => 'test_simple2',
                        'data' => [
                            'string' => 'foo1',
                            'stringNullable' => null,
                        ],
                    ],
                    1 => [
                        'name' => 'test_simple2',
                        'data' => [
                            'string' => 'bar2',
                            'stringNullable' => null,
                        ],
                    ],
                    2 => [
                        'name' => 'test_simple2',
                        'data' => [
                            'string' => 'baz3',
                            'stringNullable' => null,
                        ],
                    ],
                ],
            ],
            (function () {
                $item = new TestGroupAllowed();
                $item->items = [
                    $this->createSimple2('foo1'),
                    $this->createSimple2('bar2'),
                    $this->createSimple2('baz3'),
                ];

                return $item;
            })(),
        ];

        yield 'test_group_allowed_custom' => [
            [
                'items' => [
                    0 => [
                        'name' => 'test_custom_proxy1',
                        'data' => [
                            'prop' => 'foo1',
                        ],
                    ],
                    1 => [
                        'name' => 'test_custom_proxy2',
                        'data' => [
                            'prop' => 'bar2',
                        ],
                    ],
                ],
            ],
            (function () {
                $testProxy1 = new TestProxy('foo1');
                $testProxy1->__setMetadata(new BlockMetadata('test_custom_proxy1', 'test_custom_proxy1', TestProxy::class, new GenericType()));

                $testProxy2 = new TestProxy('bar2');
                $testProxy2->__setMetadata(new BlockMetadata('test_custom_proxy2', 'test_custom_proxy2', TestProxy::class, new GenericType()));

                $item = new TestGroupAllowedCustom();
                $item->items = [
                    $testProxy1,
                    $testProxy2,
                ];

                return $item;
            })(),
        ];

        yield 'test_object' => [
            [
                'object' => '2011-06-05 12:15:00',
                'dateTime' => '2011-06-05 12:15:00',
            ],
            (static function () {
                $item = new TestObject();
                $item->object = new \DateTime('2011-06-05 12:15:00');
                $item->dateTime = new \DateTime('2011-06-05 12:15:00');

                return $item;
            })(),
        ];

        //todo:
//        yield 'test_deprecated' => [
//            [
//                'name' => 'test_deprecated',
//                'data' => [
//                    'emptyType' => 'foo',
//                    'mixedType' => 'bar',
//                    'unionType' => 'baz',
//                ],
//            ],
//            (static function () {
//                $item = new TestDeprecated();
//                $item->emptyType = 'foo';
//                $item->mixedType = 'bar';
//                $item->unionType = 'baz';
//
//                return $item;
//            })(),
//        ];
    }

    public function testInvalidMappingStrategyThrow(): void
    {
        $item = new TestInvalidMappingStrategy();
        $item->throw = new TestBlockInterface1('foo');

        $formData = [
            'throw' => [
                'name' => 'test_interface2',
                'data' => [
                    'foo2' => 'bar',
                ],
            ],
        ];

        $this->expectException(MapperException::class);
        $this->expectExceptionMessage(sprintf('Could not map block from "%s" to "%s".', TestBlockInterface1::class, TestBlockInterface2::class));

        $form = $this->factory->create(ContentBlockType::class, $item, [
            'data_class' => TestInvalidMappingStrategy::class,
        ]);
        $form->submit($formData);
    }

    public function testInvalidMappingStrategyError(): void
    {
        $item = new TestInvalidMappingStrategy();
        $item->error = new TestBlockInterface1('foo');

        $formData = [
            'error' => [
                'name' => 'test_interface2',
                'data' => [
                    'foo2' => 'bar',
                ],
            ],
        ];

        $form = $this->factory->create(ContentBlockType::class, $item, [
            'data_class' => TestInvalidMappingStrategy::class,
        ]);
        $form->submit($formData);

        $this->assertTrue($form->isSubmitted());
        $this->assertTrue($form->isSynchronized());
        $this->assertFalse($form->isValid());
        $message = '';
        foreach ($form->getErrors(true) as $error) {
            $message .= sprintf('%s - %s', $error->getCause()->getPropertyPath(), $error->getMessage()) . PHP_EOL;
        }
        $this->assertEquals("children[error] - This value is not valid.\n", $message);
    }

    public function testInvalidMappingStrategyIgnore(): void
    {
        $item = new TestInvalidMappingStrategy();
        $item->ignore = new TestBlockInterface1('foo');

        $formData = [
            'ignore' => [
                'name' => 'test_interface2',
                'data' => [
                    'foo2' => 'bar',
                ],
            ],
        ];

        $exceptedData = new TestBlockInterface2('bar');

        $form = $this->factory->create(ContentBlockType::class, $item, [
            'data_class' => TestInvalidMappingStrategy::class,
        ]);
        $form->submit($formData);

        $this->assertTrue($form->isSubmitted());
        $this->assertTrue($form->isSynchronized());
        $message = '';
        foreach ($form->getErrors(true) as $error) {
            $message .= sprintf('%s - %s', $error->getCause()->getPropertyPath(), $error->getMessage()) . PHP_EOL;
        }
        $this->assertTrue($form->isValid(), $message);
        $this->assertEquals($exceptedData, $form->getData()->ignore);
    }

    public function testGroupBlockChangeOrder(): void
    {
        $item = new TestGroup();
        $item->array = [
            0 => new TestBlockInterface1('foo1'),
            1 => new TestBlockInterface2('foo2'),
        ];

        $formData = [
            'array' => [
                0 => [
                    'name' => 'test_interface2',
                    'data' => [
                        'foo2' => 'bar2',
                    ],
                ],
                1 => [
                    'name' => 'test_interface1',
                    'data' => [
                        'foo1' => 'bar1',
                    ],
                ],
            ],
        ];

        $exceptedData = [
            0 => new TestBlockInterface2('bar2'),
            1 => new TestBlockInterface1('bar1'),
        ];

        $form = $this->factory->create(ContentBlockType::class, $item, [
            'data_class' => TestGroup::class,
        ]);
        $form->submit($formData);

        $this->assertTrue($form->isSubmitted());
        $this->assertTrue($form->isSynchronized());
        $message = '';
        foreach ($form->getErrors(true) as $error) {
            $message .= sprintf('%s - %s', $error->getCause()->getPropertyPath(), $error->getMessage()) . PHP_EOL;
        }
        $this->assertTrue($form->isValid(), $message);
        $this->assertEquals($exceptedData, $form->getData()->array);
    }

    public function testNotRegisteredBlock(): void
    {
        $item = new TestNotRegistered();

        $this->expectExceptionMessageMatches('/Unknown content block .* The registered are.*/');

        $this->factory->create(ContentBlockType::class, $item, [
            'data_class' => TestNotRegistered::class,
        ]);
    }

    public function testValidation(): void
    {
        $this->registry->registerBlock(TestValidation::class);
        $this->registry->registerBlock(TestValidationInner::class);

        $item = new TestValidation();
        $formData = [
            'groupItemInvalid' => [
                [
                    'name' => 'test_validation_inner',
                    'data' => [
                        'foo' => '',
                    ],
                ],
            ],
        ];

        $form = $this->factory->create(ContentBlockType::class, $item, [
            'data_class' => TestValidation::class,
        ]);
        $form->submit($formData);
        $view = $form->createView();
        $errors = $this->collectFormViewErrors($view);
        self::assertEquals([
            'smol_content_block[property]' => "ERROR: This value should not be blank.\n",
            'smol_content_block[compound][foo]' => "ERROR: This value should not be blank.\n",
            'smol_content_block[provideSingle]' => "ERROR: This value should not be blank.\n",
            'smol_content_block[provide]' => "ERROR: This value should not be blank.\n",
            'smol_content_block[provideCompound][data][foo]' => "ERROR: This value should not be blank.\n",
            'smol_content_block[groupEmpty]' => "ERROR: This value should not be blank.\n",
            'smol_content_block[groupItemInvalid][0][data][foo]' => "ERROR: This value should not be blank.\n",
        ], $errors);
    }

    public function testProvideAllowChangeOnNotNullableProperty(): void
    {
        $this->registry->registerBlock(TestProvideAllowChange::class);

        $item = new TestProvideAllowChange();
        $item->allowChange = new TestBlockInterface1();

        $this->expectExceptionMessage(sprintf('Property "%s::$allowChange" should be nullable.', TestProvideAllowChange::class));

        $this->factory->create(ContentBlockType::class, $item, [
            'data_class' => TestProvideAllowChange::class,
        ]);
    }

    public function testValidationProvideGroup(): void
    {
        $this->registry->registerBlock(TestValidationProvideGroup::class);
        $this->registry->registerBlock(TestValidationProvideGroupInner::class);
        $this->registry->registerBlock(TestValidationProvideGroupInner2::class);

        $item = new TestValidationProvideGroup();
        $formData = [
            'inner' => [
                'name' => 'test_validation_provide_group_inner',
                'data' => [
                    'items1' => [
                        0 => [
                            'name' => 'test_validation_provide_group_inner2',
                            'data' => [
                                'items2' => []
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $form = $this->factory->create(ContentBlockType::class, $item, [
            'data_class' => TestValidationProvideGroup::class,
        ]);
        $form->submit($formData);
        $view = $form->createView();
        $errors = $this->collectFormViewErrors($view);
        self::assertEquals([
            'smol_content_block[inner][data][items1][0][data][items2]' => "ERROR: This collection should contain 1 element or more.\n",
        ], $errors);
    }

    public function testGroupAllowedCustomReorder(): void
    {
        $testProxy1 = new TestProxy('foo1');
        $testProxy1->__setMetadata(new BlockMetadata('test_custom_proxy1', 'test_custom_proxy1', TestProxy::class, new GenericType()));

        $testProxy2 = new TestProxy('bar2');
        $testProxy2->__setMetadata(new BlockMetadata('test_custom_proxy2', 'test_custom_proxy2', TestProxy::class, new GenericType()));

        $initialData = new TestGroupAllowedCustom();
        $initialData->items = [
            $testProxy1,
            $testProxy2,
        ];

        $formData = [
            'items' => [
                0 => [
                    'name' => 'test_custom_proxy2',
                    'data' => [
                        'prop' => 'bar2',
                    ],
                ],
                1 => [
                    'name' => 'test_custom_proxy1',
                    'data' => [
                        'prop' => 'foo1',
                    ],
                ],
            ],
        ];

        $exceptedData = new TestGroupAllowedCustom();
        $exceptedData->items = [
            $testProxy2,
            $testProxy1,
        ];

        $form = $this->factory->create(ContentBlockType::class, $initialData);
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

    private function collectFormViewErrors(FormView $view): array
    {
        $errors = [];

        if ($view->vars['errors'] && count($view->vars['errors'])) {
            $errors[$view->vars['full_name']] = $view->vars['errors']->__toString();
        }

        foreach ($view as $item) {
            foreach ($this->collectFormViewErrors($item) as $key => $error) {
                $errors[$key] = $error;
            }
        }

        return $errors;
    }

    private function createSimple2(string $string, false|string|null $stringNullable = false): TestSimple2
    {
        $item = new TestSimple2();
        $item->string = $string;

        if ($stringNullable !== false) {
            $item->stringNullable = $stringNullable;
        }

        return $item;
    }
}
