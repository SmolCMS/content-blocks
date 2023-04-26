<?php

namespace SmolCms\Bundle\ContentBlock\Serializer;

use SmolCms\Bundle\ContentBlock\ContentBlock;
use SmolCms\Bundle\ContentBlock\ResolvedProperty;
use Symfony\Component\Serializer\Context\ContextBuilderInterface;
use Symfony\Component\Serializer\Context\ContextBuilderTrait;

class ContentBlockContextBuilder implements ContextBuilderInterface
{
    use ContextBuilderTrait;

    public const DESERIALIZE = self::class . '_deserialize';
    public const OUTER_PROPERTY = self::class . '_outer_property';
    public const CONTENT_BLOCK = self::class . '_content_block';

    public function withDeserialize(): static
    {
        return $this->with(self::DESERIALIZE, true);
    }

    public function withOuterProperty(?ResolvedProperty $property): static
    {
        return $this->with(self::OUTER_PROPERTY, $property);
    }

    public function withContentBlock(?ContentBlock $block): static
    {
        return $this->with(self::CONTENT_BLOCK, $block);
    }
}
