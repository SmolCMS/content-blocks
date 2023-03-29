<?php
/*
 * @author       Kamil Smolak <kamil@smol.pl>
 * @link         http://www.smol.pl
 * @copyright    Copyright (c) 2023 Kamil Smolak
 */

namespace SmolCms\Bundle\ContentBlock\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ContentBlockValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof ContentBlock) {
            throw new UnexpectedTypeException($constraint, ContentBlock::class);
        }

        $context = $this->context;

        $validator = $context->getValidator()->inContext($context);

        $validator->validate($value['properties']['value'], $constraint->constraints);
    }
}
