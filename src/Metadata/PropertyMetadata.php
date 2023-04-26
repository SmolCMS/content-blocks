<?php

/*
 * @author       Kamil Smolak <kamil@smol.pl>
 * @link         http://www.smol.pl
 * @copyright    Copyright (c) 2023 Kamil Smolak
 */

namespace SmolCms\Bundle\ContentBlock\Metadata;

use SmolCms\Bundle\ContentBlock\Attribute\Property;

readonly class PropertyMetadata
{
    public function __construct(
        private string $class,
        private string $propertyName,
        private Property $property,
        private string $propertyType,
        private bool $nullable,
        private bool $isBuiltin,
    ) {
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getPropertyName(): string
    {
        return $this->propertyName;
    }

    public function getProperty(): Property
    {
        return $this->property;
    }

    public function getPropertyType(): string
    {
        return $this->propertyType;
    }

    public function isNullable(): bool
    {
        return $this->nullable;
    }

    public function isBuiltin(): bool
    {
        return $this->isBuiltin;
    }
}
