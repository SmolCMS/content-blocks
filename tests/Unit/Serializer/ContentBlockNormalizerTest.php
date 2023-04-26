<?php

namespace SmolCms\Bundle\ContentBlock\Tests\Unit\Serializer;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use SmolCms\Bundle\ContentBlock\ContentBlockFactory;
use SmolCms\Bundle\ContentBlock\Mapper\CompositeMapper;
use SmolCms\Bundle\ContentBlock\Mapper\FromBuiltinMapper;
use SmolCms\Bundle\ContentBlock\Mapper\GroupToArrayMapper;
use SmolCms\Bundle\ContentBlock\Mapper\MapperInterface;
use SmolCms\Bundle\ContentBlock\Metadata\BlockMetadata;
use SmolCms\Bundle\ContentBlock\Metadata\MetadataRegistry;
use SmolCms\Bundle\ContentBlock\ResolvedBlockFactory;
use SmolCms\Bundle\ContentBlock\ResolvedPropertyFactory;
use SmolCms\Bundle\ContentBlock\Serializer\BuiltinDenormalizer;
use SmolCms\Bundle\ContentBlock\Serializer\ContentBlockDenormalizer;
use SmolCms\Bundle\ContentBlock\Serializer\ContentBlockNormalizer;
use SmolCms\Bundle\ContentBlock\Serializer\GroupDenormalizer;
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
use SmolCms\Bundle\ContentBlock\Tests\Fixtures\Block\TestIterable;
use SmolCms\Bundle\ContentBlock\Tests\Fixtures\Block\TestMixed;
use SmolCms\Bundle\ContentBlock\Tests\Fixtures\Block\TestNotNullable;
use SmolCms\Bundle\ContentBlock\Tests\Fixtures\Block\TestObject;
use SmolCms\Bundle\ContentBlock\Tests\Fixtures\Block\TestProvide;
use SmolCms\Bundle\ContentBlock\Tests\Fixtures\Block\TestProxy;
use SmolCms\Bundle\ContentBlock\Tests\Fixtures\Block\TestReadonly;
use SmolCms\Bundle\ContentBlock\Tests\Fixtures\Block\TestSimple;
use SmolCms\Bundle\ContentBlock\Tests\Fixtures\Block\TestSimple2;
use SmolCms\Bundle\ContentBlock\Tests\Fixtures\Block\TestUninitialized;
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
use Symfony\Component\Form\FormTypeGuesserChain;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ContentBlockNormalizerTest extends TestCase
{
    private MetadataRegistry $registry;
    private TypeHandlerFactory $handlerFactory;
    private ResolvedPropertyFactory $propertyFactory;
    private ResolvedBlockFactory $blockFactory;
    private MapperInterface $mapper;
    private NormalizerInterface $normalizer;
    private DenormalizerInterface $denormalizer;
    private ContentBlockFactory $contentBlockFactory;
    private ValidatorInterface $validator;

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
            'test_iterable' => [
                'class' => TestIterable::class,
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
        $this->validator = Validation::createValidator();
        $this->propertyFactory = new ResolvedPropertyFactory($this->registry, $this->validator);
        $this->blockFactory = new ResolvedBlockFactory($this->propertyFactory);
        $this->mapper = new CompositeMapper([
            new FromBuiltinMapper(),
            new GroupToArrayMapper(),
        ]);
        $this->contentBlockFactory = new ContentBlockFactory($this->registry);

        $container = $this->createMock(ContainerInterface::class);
        $this->normalizer = new ContentBlockNormalizer($this->registry, $this->blockFactory, $container);
        $this->denormalizer = new ContentBlockDenormalizer($this->contentBlockFactory, $this->blockFactory, $this->mapper, $container);
        $container->method('get')
            ->willReturn(new Serializer([
                $this->normalizer,
                $this->denormalizer,
                new BuiltinDenormalizer(),
                new GroupDenormalizer($this->contentBlockFactory, $container),
                new DateTimeNormalizer(),
                new ObjectNormalizer(),
            ]));
    }

    /**
     * @dataProvider getNormalizeData
     */
    public function testNormalize(mixed $object, array $excepted): void
    {
        $result = $this->normalizer->normalize($object);

        self::assertEquals($excepted, $result);
    }

    /**
     * @dataProvider getDenormalizeData
     */
    public function testDenormalize(array $data, mixed $excepted): void
    {
        $result = $this->denormalizer->denormalize($data, get_class($excepted));

        self::assertEquals($excepted, $result);
    }

    public function getNormalizeData(): iterable
    {
        yield 'test_builtin' => [
            (static function () {
                $item = new TestBuiltin();
                $item->string = 'foo';
                $item->int = 123;
                $item->float = 123.321;
                $item->bool = true;

                return $item;
            })(),
            [
                'name' => 'test_builtin',
                'data' => [
                    'string' => 'foo',
                    'int' => 123,
                    'float' => 123.321,
                    'bool' => true,
                ],
            ],
        ];

        yield 'test_mixed' => [
            (static function () {
                $item = new TestMixed();
                $item->emptyType = 'foo';
                $item->mixedType = 'bar';
                $item->unionType = 'baz';

                return $item;
            })(),
            [
                'name' => 'test_mixed',
                'data' => [
                    'emptyType' => 'foo',
                    'mixedType' => 'bar',
                    'unionType' => 'baz',
                ],
            ],
        ];

        yield 'test_simple2' => [
            $this->createSimple2('foo'),
            [
                'name' => 'test_simple2',
                'data' => [
                    'string' => 'foo',
                    'stringNullable' => null,
                ],
            ],
        ];

        yield 'test_simple' => [
            (function () {
                $item = new TestSimple();
                $item->string = 'bar';
                $item->stringNullable = null;
                $item->innerSimple = $this->createSimple2('baz');
                $item->formOnProperty = 'formOnPropertyVal';

                return $item;
            })(),
            [
                'name' => 'test_simple',
                'data' => [
                    'string' => 'bar',
                    'stringNullable' => null,
                    'innerSimple' => [
                        'name' => 'test_simple2',
                        'data' => [
                            'string' => 'baz',
                            'stringNullable' => null,
                        ],
                    ],
                    'formOnProperty' => 'formOnPropertyVal',
                ],
            ],
        ];

        yield 'test_group_provide' => [
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
            [
                'name' => 'test_group_provide',
                'data' => [
                    'property' => [
                        'name' => 'group',
                        'data' => [
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
                    'customType' => [
                        'name' => 'group',
                        'data' => [
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
                ],
            ],
        ];

        yield 'test_provide' => [
            (function () {
                $item = new TestProvide();
                $item->string = 'foo';
                $item->compound = $this->createSimple2('bar');
                $item->emptyTypeAllowed = $this->createSimple2('baz');
                $item->interface = new TestBlockInterface2('bar');

                return $item;
            })(),
            [
                'name' => 'test_provide',
                'data' => [
                    'string' => 'foo',
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
                    'interface' => [
                        'name' => 'test_interface2',
                        'data' => [
                            'foo2' => 'bar',
                        ],
                    ],
                ],
            ],
        ];

        yield 'test_group' => [
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
            [
                'name' => 'test_group',
                'data' => [
                    'array' => [
                        'name' => 'group',
                        'data' => [
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
                    'iterable' => [
                        'name' => 'group',
                        'data' => [
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
                    'group' => [
                        'name' => 'group',
                        'data' => [
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
                ],
            ],
        ];

        yield 'test_group_allowed' => [
            (function () {
                $item = new TestGroupAllowed();
                $item->items = [
                    $this->createSimple2('foo1'),
                    $this->createSimple2('bar2'),
                    $this->createSimple2('baz3'),
                ];

                return $item;
            })(),
            [
                'name' => 'test_group_allowed',
                'data' => [
                    'items' => [
                        'name' => 'group',
                        'data' => [
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
                ],
            ],
        ];

        yield 'test_group_allowed_custom' => [
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
            [
                'name' => 'test_group_allowed_custom',
                'data' => [
                    'items' => [
                        'name' => 'group',
                        'data' => [
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
                ],
            ],
        ];

        yield 'test_object' => [
            (static function () {
                $item = new TestObject();
                $item->object = new \DateTime('2011-06-05 12:15:00');
                $item->dateTime = new \DateTime('2011-06-05 12:15:00');

                return $item;
            })(),
            [
                'name' => 'test_object',
                'data' => [
                    'object' => '2011-06-05T12:15:00+00:00',
                    'dateTime' => '2011-06-05T12:15:00+00:00',
                ],
            ],
        ];

        yield 'test_uninitialized' => [
            (static function () {
                return new TestUninitialized();
            })(),
            [
                'name' => 'test_uninitialized',
                'data' => [],
            ],
        ];

        yield 'test_getsetis' => [
            (static function () {
                $item = new TestGetSetIs();
                $item->setString('foo');
                $item->setBool(true);

                return $item;
            })(),
            [
                'name' => 'test_getsetis',
                'data' => [
                    'string' => 'foo',
                    'bool' => true,
                ],
            ],
        ];

        yield 'test_readonly' => [
            new TestReadonly('foo'),
            [
                'name' => 'test_readonly',
                'data' => [
                    'string' => 'foo',
                ],
            ],
        ];

        yield 'test_iterable' => [
            new TestIterable([
                'foo',
                'bar',
            ], [
                'foo',
                'bar',
            ]),
            [
                'name' => 'test_iterable',
                'data' => [
                    'iterable' => [
                        'foo',
                        'bar',
                    ],
                    'array' => [
                        'foo',
                        'bar',
                    ],
                ],
            ],
        ];
    }

    public function getDenormalizeData(): iterable
    {
        foreach ($this->getNormalizeData() as $name => [$excepted, $array]) {
            yield $name => [$array, $excepted];
        }

        yield 'test_default' => [
            [
                'name' => 'test_default',
                'data' => [],
            ],
            new TestDefault(),
        ];

        yield 'skip unused properties' => [
            [
                'name' => 'test_simple',
                'data' => [
                    'skip' => 'foo',
                ],
            ],
            new TestSimple(),
        ];
    }

    public function testDenormalizeNotNullable(): void
    {
        $data = [
            'name' => 'test_not_nullable',
            'data' => [
                'foo' => null,
            ],
        ];

        $this->expectExceptionMessage(sprintf('Property %s::foo is not nullable.', TestNotNullable::class));

        $this->registry->registerBlock(TestNotNullable::class);
        $this->denormalizer->denormalize($data, TestNotNullable::class);
    }

    public function testDenormalizeNotNullableSkipWithoutPropertyDataSet(): void
    {
        $data = [
            'name' => 'test_not_nullable',
            'data' => [],
        ];

        $this->registry->registerBlock(TestNotNullable::class);
        $result = $this->denormalizer->denormalize($data, TestNotNullable::class);

        self::assertEquals($result, new TestNotNullable());
    }

    public function testDenormalizeNotAllowedBlockInGroup(): void
    {
        $data = [
            'name' => 'test_group_allowed',
            'data' => [
                'items' => [
                    'name' => 'group',
                    'data' => [
                        [
                            'name' => 'test_not_allowed',
                            'data' => [],
                        ],
                    ],
                ],
            ],
        ];

        $this->expectExceptionMessage('Not allowed content block "test_not_allowed". Allowed are: test_simple2.');

        $this->registry->registerBlock(TestGroupAllowed::class);
        $this->denormalizer->denormalize($data, TestGroupAllowed::class);
    }

    private function createSimple2(string $string): TestSimple2
    {
        $item = new TestSimple2();
        $item->string = $string;
        $item->stringNullable = null;

        return $item;
    }
}
