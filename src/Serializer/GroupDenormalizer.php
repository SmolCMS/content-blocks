<?php

namespace SmolCms\Bundle\ContentBlock\Serializer;

use Psr\Container\ContainerInterface;
use SmolCms\Bundle\ContentBlock\ContentBlockFactory;
use SmolCms\Bundle\ContentBlock\ResolvedProperty;
use SmolCms\Bundle\ContentBlock\Type\Group;
use SmolCms\Bundle\ContentBlock\Type\GroupType;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

readonly class GroupDenormalizer implements DenormalizerInterface, ServiceSubscriberInterface
{
    public function __construct(
        private ContentBlockFactory $contentBlockFactory,
        private ContainerInterface $container,
    ) {
    }

    public function denormalize(mixed $data, string $type, string $format = null, array $context = []): Group
    {
        $data = $data['data'] ?? $data;
        $items = [];

        $allowedBlocks = null;
        $blockType = $this->getOuterProperty($context)?->getType();
        if ($blockType instanceof GroupType) {
            $allowedBlocks = $blockType->getAllowedBlocks();
        }

        foreach ($data as $item) {
            $innerBlock = $this->contentBlockFactory->create($item, allowedBlocks: $allowedBlocks);
            $object = $this->getDenormalizer()->denormalize($innerBlock, $innerBlock->getMetadata()->class);
            $items[] = $object;
        }

        return new Group($items);
    }

    public function supportsDenormalization(mixed $data, string $type, string $format = null, array $context = []): bool
    {
        if (is_a($type, Group::class, true)) {
            return true;
        }

        $innerType = $this->getOuterProperty($context)?->getInnerBlockMetadata()?->class;
        if (!$innerType) {
            return false;
        }

        return is_a($innerType, Group::class, true) && Group::supports($type);
    }

    public static function getSubscribedServices(): array
    {
        return [
            DenormalizerInterface::class,
        ];
    }

    private function getDenormalizer(): DenormalizerInterface
    {
        return $this->container->get(DenormalizerInterface::class);
    }

    private function getOuterProperty(array $context): ?ResolvedProperty
    {
        return $context[ContentBlockContextBuilder::OUTER_PROPERTY] ?? null;
    }
}
