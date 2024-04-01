<?php

namespace PHPZlc\PHPZlc\Bundle\Safety;

use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ObjectManager;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CommandLoad
{
    public function __construct(ContainerInterface $container, ObjectManager $objectManager, ParameterBagInterface $parameterBag, Connection $connection, ValidatorInterface $validator)
    {
        ActionLoad::$globalContainer = $container;
        ActionLoad::$globalDoctrine = $objectManager;
        ActionLoad::$globalParameter = $parameterBag;
        ActionLoad::$globalConnection = $connection;
        ActionLoad::$globalValidation = $validator;
    }

    public function onCommand(ConsoleCommandEvent $event)
    {
    }
}