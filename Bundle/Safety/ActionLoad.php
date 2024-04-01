<?php
/**
 * Created by PhpStorm.
 * User: Jay
 * Date: 9/24/20
 * Time: 7:44 PM
 */
namespace PHPZlc\PHPZlc\Bundle\Safety;

use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ObjectManager;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ActionLoad implements EventSubscriberInterface
{

    /**
     * @var ContainerInterface
     */
    public static $globalContainer;

    /**
     * @var ObjectManager
     */
    public static $globalDoctrine;

    /**
     * @var ParameterBagInterface
     */
    public static $globalParameter;

    /**
     * @var Connection
     */
    public static $globalConnection;

    /**
     * @var ValidatorInterface
     */
    public static $globalValidation;

    public function __construct(ContainerInterface $container, ObjectManager $objectManager, ParameterBagInterface $parameterBag, Connection $connection, ValidatorInterface $validator)
    {
        self::$globalContainer = $container;
        self::$globalDoctrine = $objectManager;
        self::$globalParameter = $parameterBag;
        self::$globalConnection = $connection;
        self::$globalValidation = $validator;
    }

    public function onKernelController(ControllerEvent $event)
    {
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }
}