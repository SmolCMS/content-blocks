<?php

namespace SmolCms\Bundle\ContentBlock\Mapper;

enum InvalidMappingStrategy: string
{
    case THROW = 'throw';
    case ERROR = 'error';
    case IGNORE = 'ignore';
}
