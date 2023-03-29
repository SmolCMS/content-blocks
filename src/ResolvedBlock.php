<?php

/*
 * @author       Kamil Smolak <kamil@smol.pl>
 * @link         http://www.smol.pl
 * @copyright    Copyright (c) 2023 Kamil Smolak
 */

namespace SmolCms\Bundle\ContentBlock;

use SmolCms\Bundle\ContentBlock\Metadata\BlockMetadata;
use SmolCms\Bundle\ContentBlock\Type\TypeInterface;

readonly class ResolvedBlock
{
    /**
     * @param ResolvedProperty[] $properties
     */
    public function __construct(
        private ?ResolvedProperty $outerProperty,
        private BlockMetadata $metadata,
        private array $properties,
    ) {
    }

    public function getOuterProperty(): ?ResolvedProperty
    {
        return $this->outerProperty;
    }

    public function getMetadata(): BlockMetadata
    {
        return $this->metadata;
    }

    public function getProperties(): array
    {
        return $this->properties;
    }

    public function getType(): TypeInterface
    {
        return $this->outerProperty?->getType() ?? $this->metadata->type;
    }

    public function getFormOptions(): array
    {
        if ($this->getOuterProperty()) {
            return $this->getOuterProperty()->getFormOptions();
        }

        $type = $this->getType();

        return $type->formOptions + [
            'label' => $type->label ?? $this->metadata->name,
            'required' => $type->required,
        ];
    }
}
