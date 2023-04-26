<?php

namespace SmolCms\Bundle\ContentBlock\Form\Transformer;

use SmolCms\Bundle\ContentBlock\Type\Group;
use Symfony\Component\Form\DataTransformerInterface;

class GroupBlockTransformer implements DataTransformerInterface
{
    public function transform(mixed $value): mixed
    {
        if (!$value) {
            return null;
        }

        if ($value instanceof Group) {
            return $value->items;
        }

        return $value;
    }

    public function reverseTransform(mixed $value): ?Group
    {
        if (!$value) {
            return null;
        }

        $item = new Group();
        $item->items = $value;

        return $item;
    }
}
