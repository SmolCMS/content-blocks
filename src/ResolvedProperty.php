<?php

/*
 * @author       Kamil Smolak <kamil@smol.pl>
 * @link         http://www.smol.pl
 * @copyright    Copyright (c) 2023 Kamil Smolak
 */

namespace SmolCms\Bundle\ContentBlock;

use SmolCms\Bundle\ContentBlock\Metadata\BlockMetadata;
use SmolCms\Bundle\ContentBlock\Metadata\PropertyMetadata;
use SmolCms\Bundle\ContentBlock\Type\GenericType;
use SmolCms\Bundle\ContentBlock\Type\TypeInterface;
use Symfony\Component\Validator\Constraint;

readonly class ResolvedProperty
{
    public function __construct(
        private BlockMetadata $parentBlockMetadata,
        private PropertyMetadata $metadata,
        private ?BlockMetadata $innerBlockMetadata,
        private array $constraints,
    ) {
    }

    public function getParentBlockMetadata(): BlockMetadata
    {
        return $this->parentBlockMetadata;
    }

    public function getMetadata(): PropertyMetadata
    {
        return $this->metadata;
    }

    public function getClass(): string
    {
        return $this->metadata->getClass();
    }

    public function getPropertyName(): string
    {
        return $this->metadata->getPropertyName();
    }

    public function getInnerBlockMetadata(): ?BlockMetadata
    {
        return $this->innerBlockMetadata;
    }

    public function getType(): TypeInterface
    {
        return $this->metadata->getProperty()->type ?? $this->innerBlockMetadata?->type ?? new GenericType();
    }

    public function getLabel(): string|bool
    {
        return $this->getType()->label ?? $this->getPropertyName();
    }

    public function isRequired(): bool
    {
        return $this->getType()->required ?? !$this->metadata->isNullable();
    }

    /**
     * @return Constraint[]
     */
    public function getConstraints(): array
    {
        return $this->constraints;
    }
}
