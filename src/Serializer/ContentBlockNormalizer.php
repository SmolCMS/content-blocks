<?php

/*
 * @author       Kamil Smolak <kamil@smol.pl>
 * @link         http://www.smol.pl
 * @copyright    Copyright (c) 2023 Kamil Smolak
 */

namespace SmolCms\Bundle\ContentBlock\Serializer;

use Psr\Container\ContainerInterface;
use SmolCms\Bundle\ContentBlock\Metadata\BlockMetadata;
use SmolCms\Bundle\ContentBlock\Metadata\MetadataReaderException;
use SmolCms\Bundle\ContentBlock\Metadata\MetadataRegistry;
use SmolCms\Bundle\ContentBlock\ResolvedBlockFactory;
use SmolCms\Bundle\ContentBlock\ResolvedProperty;
use SmolCms\Bundle\ContentBlock\Type\Builtin;
use SmolCms\Bundle\ContentBlock\Type\Group;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class ContentBlockNormalizer implements NormalizerInterface, ServiceSubscriberInterface
{
    private PropertyAccessor $propertyAccessor;

    public function __construct(
        private readonly MetadataRegistry $registry,
        private readonly ResolvedBlockFactory $resolvedBlockFactory,
        private readonly ContainerInterface $container,
    ) {
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
    }

    public function normalize(mixed $object, string $format = null, array $context = []): mixed
    {
        $outerProperty = $context[ContentBlockContextBuilder::OUTER_PROPERTY] ?? null;
        $blockMetadata = $this->resolveBlockMetadata($outerProperty, $object);
        if (!$blockMetadata) {
            $normalizer = $this->getNormalizer();
            if (!$normalizer->supportsNormalization($object, $format)) {
                throw new \LogicException(sprintf('Could not normalize value of type "%s". No supported normalizer found.', get_debug_type($object)));
            }

            return $normalizer->normalize($object, $format);
        }

        if ($blockMetadata->class === Builtin::class) {
            return $object;
        }

        $resolvedBlock = $this->resolvedBlockFactory->create($outerProperty, $blockMetadata);

        $properties = [];
        foreach ($resolvedBlock->getProperties() as $resolvedProperty) {
            $name = $resolvedProperty->getPropertyName();
            if (!$this->propertyAccessor->isReadable($object, $name)) {
                continue;
            }

            $value = $this->propertyAccessor->getValue($object, $name);
            $normalizedValue = $this->resolveValue($resolvedProperty, $value, $format);

            $properties[$name] = $normalizedValue;
        }

        return [
            'name' => $blockMetadata->name,
            'data' => $properties,
        ];
    }

    public function supportsNormalization(mixed $data, string $format = null, array $context = []): bool
    {
        return is_object($data) && $this->registry->has(get_class($data));
    }

    private function resolveBlockMetadata(?ResolvedProperty $outerProperty, mixed $object): ?BlockMetadata
    {
        $blockMetadata = $outerProperty?->getInnerBlockMetadata();
        if ($blockMetadata) {
            return $blockMetadata;
        }

        if (is_object($object)) {
            try {
                return $this->registry->metadataFor($object);
            } catch (MetadataReaderException) {
                return null;
            }
        }

        if (Builtin::supports(gettype($object))) {
            return $this->registry->metadataFor(Builtin::class);
        }

        return null;
    }

    private function getNormalizer(): NormalizerInterface
    {
        return $this->container->get(NormalizerInterface::class);
    }

    public static function getSubscribedServices(): array
    {
        return [
            NormalizerInterface::class,
        ];
    }

    private function resolveValue(ResolvedProperty $resolvedProperty, mixed $value, ?string $format): mixed
    {
        $innerBlockMetadata = $resolvedProperty->getInnerBlockMetadata();

        if ($innerBlockMetadata) {
            if (is_a($innerBlockMetadata->class, Builtin::class, true)) {
                return $value;
            }

            if (is_a($innerBlockMetadata->class, Group::class, true)) {
                $items = [];

                foreach ($value as $key => $item) {
                    $items[$key] = $this->getNormalizer()->normalize($item, $format);
                }

                return [
                    'name' => 'group',
                    'data' => $items,
                ];
            }
        }

        $context = (new ContentBlockContextBuilder())
            ->withOuterProperty($resolvedProperty)
            ->toArray();

        return $this->getNormalizer()->normalize($value, $format, $context);
    }
}
