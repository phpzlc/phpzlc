<?php
/**
 * PhpStorm.
 * User: Jay
 * Date: 2019/10/17
 */

namespace PHPZlc\PHPZlc\Bundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class SystemBaseController extends AbstractController
{
    const RETURN_SHOW_RESOURCE = 'SHOW_RESOURCE';

    const RETURN_HIDE_RESOURCE = 'HIDE_RESOURCE';

    private static $returnType;

    public static function setReturnType($returnType)
    {
        self::$returnType = $returnType;
    }

    public static function getReturnType()
    {
        return self::$returnType;
    }

    protected function inlet($returnType = SystemBaseController::RETURN_HIDE_RESOURCE, $isLogin = true)
    {
        SystemBaseController::setReturnType($returnType);

        return true;
    }
}