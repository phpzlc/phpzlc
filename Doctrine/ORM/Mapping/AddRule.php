<?php
/**
 * PhpStorm.
 * User: Jay
 * Date: 2019/8/8
 */

declare(strict_types=1);

namespace PHPZlc\PHPZlc\Doctrine\ORM\Mapping;

use Attribute;
use Doctrine\ORM\Mapping\MappingAttribute;
use PHPZlc\PHPZlc\Doctrine\ORM\Rule\InterfaceJoint;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class AddRule implements MappingAttribute
{
    public function __construct(
        public readonly string|null $name = null,
        public readonly string|null $value = null,
        public readonly string|null $collision = null,
        public readonly InterfaceJoint|null $jointClass = null,
        public readonly string|null $jointSort = null,
    ){
    }
}