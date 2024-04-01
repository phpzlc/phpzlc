<?php
/**
 * PhpStorm.
 * User: Jay
 * Date: 2019/7/24
 */

namespace PHPZlc\PHPZlc\Bundle\Business;


use Doctrine\DBAL\Connection;
use PHPZlc\PHPZlc\Bundle\Safety\ActionLoad;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use PHPZlc\PHPZlc\Abnormal\Errors;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class AbstractBusiness extends AbstractController
{
    /**
     * @var \Doctrine\Persistence\ObjectManager
     */
    public $em;

    /**
     * @var Connection
     */
    public $conn;

    /**
     * @var ValidatorInterface
     */
    private static $validation;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->em = ActionLoad::$globalDoctrine;
        $this->conn = ActionLoad::$globalConnection;

        if(empty(self::$validation)){
            self::$validation = ActionLoad::$globalValidation;
        }
    }

    protected function getParameter(string $name): array|bool|string|int|float|\UnitEnum|null
    {
        if($this->container->has('parameter_bag')){
            return parent::getParameter($name);
        }

        return ActionLoad::$globalParameter->get($name);
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

    public function validator($class) : bool
    {
        return Errors::validate(self::$validation, $class);
    }

    protected function networkError(\Exception $exception)
    {
        return Errors::exceptionError($exception);
    }
}
