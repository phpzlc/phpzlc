<?php
/**
 * PhpStorm.
 * User: Jay
 * Date: 2019/10/17
 */

namespace PHPZlc\Kernel\Bundle\SfProgramBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class SystemBaseController extends AbstractController
{
    const RETURN_RESOURCE = 'resource';

    const RETURN_MESSAGE = 'message';

    private static $returnType;

    public static function setReturnType($returnType)
    {
        static::$returnType = $returnType;
    }

    public static function getReturnType()
    {
        return static::$returnType;
    }
}