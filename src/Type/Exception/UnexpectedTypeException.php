<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SmolCms\Bundle\ContentBlock\Type\Exception;

class UnexpectedTypeException extends \Exception
{
    public function __construct(mixed $value, string $expectedType)
    {
        parent::__construct(sprintf('Expected type "%s", "%s" given', $expectedType, get_debug_type($value)));
    }
}
