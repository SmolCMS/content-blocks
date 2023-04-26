<?php

namespace SmolCms\Bundle\ContentBlock\Form\Transformer;

use SmolCms\Bundle\ContentBlock\Mapper\InvalidMappingStrategy;
use SmolCms\Bundle\ContentBlock\Mapper\MapperException;
use SmolCms\Bundle\ContentBlock\Mapper\MapperInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

readonly class MapperTransformer implements DataTransformerInterface
{
    public function __construct(
        private MapperInterface $mapper,
        private string $dataClass,
        private InvalidMappingStrategy $invalidMappingStrategy,
    ) {
    }

    /**
     * @throws MapperException
     */
    public function transform(mixed $value): mixed
    {
        if (!$value) {
            return null;
        }

        if ($value instanceof $this->dataClass) {
            return $value;
        }

        try {
            return $this->mapper->map($value, $this->dataClass);
        } catch (MapperException $exception) {
            switch ($this->invalidMappingStrategy) {
                case InvalidMappingStrategy::ERROR:
                    throw new TransformationFailedException($exception->getMessage(), previous: $exception);
                case InvalidMappingStrategy::IGNORE:
                    return null;
                case InvalidMappingStrategy::THROW:
                default:
                    throw $exception;
            }
        }
    }

    public function reverseTransform(mixed $value): mixed
    {
        return $value;
    }
}
