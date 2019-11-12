<?php
/**
 * PhpStorm.
 * User: Jay
 * Date: 2019/8/8
 */

namespace PHPZlc\Kernel\Doctrine\ORM\Mapping;

use Doctrine\Common\Annotations\Annotation\Target;
use Doctrine\ORM\Mapping\Annotation;

/**
 * Class OuterColumn
 * @package PHPZlc\Kernel\Doctrine\ORM\Mapping\OuterColumn
 * @Annotation
 * @Target({"PROPERTY"})
 */
final class AddRule implements Annotation
{
    /**
     * 规则名称
     *
     * @var string
     */
    public $name;

    /**
     * 规则内容
     */
    public $value;

    /**
     * 碰撞规则
     *
     * @var string
     */
    public $collision;

    /**
     * @var JointInterface
     */
    public $jointClass;

    /**
     * @var string
     */
    public $jointSort;
}