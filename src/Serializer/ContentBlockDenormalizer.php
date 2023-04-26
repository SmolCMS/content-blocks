<?php

/*
 * @author       Kamil Smolak <kamil@smol.pl>
 * @link         http://www.smol.pl
 * @copyright    Copyright (c) 2023 Kamil Smolak
 */

namespace SmolCms\Bundle\ContentBlock\Serializer;

use Psr\Container\ContainerInterface;
use SmolCms\Bundle\ContentBlock\ContentBlock;
use SmolCms\Bundle\ContentBlock\ContentBlockFactory;
use SmolCms\Bundle\ContentBlock\Mapper\MapperInterface;
use SmolCms\Bundle\ContentBlock\Proxy\ContentBlockProxyInterface;
use SmolCms\Bundle\ContentBlock\ResolvedBlockFactory;
use SmolCms\Bundle\ContentBlock\ResolvedProperty;
use SmolCms\Bundle\ContentBlock\Type\Builtin;
use SmolCms\Bundle\ContentBlock\Type\Group;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

readonly class ContentBlockDenormalizer implements DenormalizerInterface, ServiceSubscriberInterface
{
    public function __construct(
        private ContentBlockFactory $contentBlockFactory,
        private ResolvedBlockFactory $resolvedBlockFactory,
        private MapperInterface $mapper,
        private ContainerInterface $container,
    ) {
    }

    public function denormalize(mixed $data, string $type, string $format = null, array $context = []): mixed
    {
        $block = $data instanceof ContentBlock ? $data : $this->contentBlockFactory->create($data, $type);
        assert($block instanceof ContentBlock);

        $values = $this->resolveValues($block);
        $className = $block->getMetadata()->class;
        $object = $this->getDenormalizer()->denormalize($values, $className);

        if ($object instanceof ContentBlockProxyInterface) {
            $object->__setMetadata($block->getMetadata());
        }

        return $this->finalizeValue($object, $type);
    }

    public function supportsDenormalization(mixed $data, string $type, string $format = null, array $context = []): bool
    {
        return $data instanceof ContentBlock || ($context[ContentBlockContextBuilder::DESERIALIZE] ?? false);
    }

    private function resolveValues(ContentBlock $block): array
    {
        $blockMetadata = $block->getMetadata();
        $data = $block->getData();
        if (is_a($blockMetadata->class, Group::class, true)) {
            return $data;
        }

        if (is_a($blockMetadata->class, Builtin::class, true)) {
            return $data;
        }

        $resolvedBlock = $this->resolvedBlockFactory->create(null, $blockMetadata);
        $resolvedValues = [];

        foreach ($resolvedBlock->getProperties() as $property) {
            $propertyName = $property->getPropertyName();
            if (!array_key_exists($propertyName, $data)) {
                continue;
            }

            $value = $data[$propertyName] ?? null;
            $resolvedValues[$propertyName] = $this->resolvePropertyValue($property, $value);
        }

        return $resolvedValues;
    }

    private function resolvePropertyValue(ResolvedProperty $property, mixed $value): mixed
    {
        $propertyMetadata = $property->getMetadata();

        if (is_null($value)) {
            if (!$propertyMetadata->isNullable()) {
                throw new \InvalidArgumentException(
                    sprintf('Property %s::%s is not nullable.', $property->getClass(), $property->getPropertyName())
                );
            }

            return null;
        }

        $denormalizer = $this->getDenormalizer();
        $propertyType = $propertyMetadata->getPropertyType();
        $denormalize = $propertyMetadata->getProperty()->denormalize;
        if ($denormalize) {
            return $denormalizer->denormalize($value, $denormalize);
        }

        if ($property->getMetadata()->isBuiltin()) {
            $innerContext = (new ContentBlockContextBuilder())
                ->withOuterProperty($property)
                ->toArray();

            if ($denormalizer->supportsDenormalization($value, $propertyType, null, $innerContext)) {
                $denormalized = $denormalizer->denormalize($value, $propertyType, null, $innerContext);

                return $this->finalizeValue($denormalized, $propertyType);
            }
        }

        $innerBlock = $this->contentBlockFactory->create($value);
        $innerType = $innerBlock->getMetadata()->class;
        $innerContext = (new ContentBlockContextBuilder())
            ->withContentBlock($innerBlock)
            ->withOuterProperty($property)
            ->toArray();

        if ($denormalizer->supportsDenormalization($innerBlock, $innerType, null, $innerContext)) {
            $denormalized = $denormalizer->denormalize($innerBlock, $innerType, null, $innerContext);

            return $this->finalizeValue($denormalized, $propertyType);
        }

        return $value;
    }

    private function finalizeValue(mixed $value, string $type): mixed
    {
        if ($type === 'mixed' || is_a($value, $type)) {
            return $value;
        }

        if ($this->mapper->supports($value, $type)) {
            return $this->mapper->map($value, $type);
        }

        return $value;
    }

    private function getDenormalizer(): DenormalizerInterface
    {
        return $this->container->get(DenormalizerInterface::class);
    }

    public static function getSubscribedServices(): array
    {
        return [
            DenormalizerInterface::class,
        ];
    }
}
