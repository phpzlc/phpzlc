<?php

declare(strict_types=1);

namespace PHPZlc\PHPZlc\Doctrine\ORM\Mapping;

use Attribute;
use Doctrine\ORM\Mapping\MappingAttribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class OuterColumn implements MappingAttribute
{
    public function __construct(
        public readonly string|null $name = null,
        public readonly string|null $type = 'string',
        public readonly string|null $sql = null,
        public readonly array $options = [],
    ){
    }
}