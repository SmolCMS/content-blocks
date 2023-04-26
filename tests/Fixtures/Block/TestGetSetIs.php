<?php

namespace SmolCms\Bundle\ContentBlock\Tests\Fixtures\Block;

use SmolCms\Bundle\ContentBlock\Attribute\AsContentBlock;
use SmolCms\Bundle\ContentBlock\Attribute\Property;

#[AsContentBlock('test_getsetis')]
class TestGetSetIs
{
    #[Property]
    private ?string $string = null;

    #[Property]
    private bool $bool = false;

    public function getString(): ?string
    {
        return $this->string;
    }

    public function setString(?string $string): void
    {
        $this->string = $string;
    }

    public function isBool(): bool
    {
        return $this->bool;
    }

    public function setBool(bool $bool): void
    {
        $this->bool = $bool;
    }
}
