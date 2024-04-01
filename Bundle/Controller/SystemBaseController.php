<?php
/**
 * PhpStorm.
 * User: Jay
 * Date: 2019/10/17
 */

namespace PHPZlc\PHPZlc\Bundle\Controller;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
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

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @throws NotFoundExceptionInterface  No entry was found for **this** identifier.
     * @throws ContainerExceptionInterface Error while retrieving the entry.
     *
     * @return mixed Entry.
     */
    public function get(string $id)
    {
        switch ($id) {
            case 'session':
                return $this->container->get('request_stack')->getCurrentRequest()->getSession();
        }

        return $this->container->get($id);
    }
}