<?php

namespace PHPZlc\Kernel\Doctrine\ORM\Mapping;

use Doctrine\Common\Annotations\Annotation\Target;
use Doctrine\ORM\Mapping\Annotation;

/**
 * Class OuterColumn
 * @package PHPZlc\Kernel\Doctrine\ORM\Mapping\OuterColumn
 * @Annotation
 * @Target({"PROPERTY"})
 */
final class OuterColumn implements Annotation
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var mixed
     */
    public $type = 'string';

    /**
     * @var string
     */
    public $sql;

    /**
     * @var array
     */
    public $options = [];
}