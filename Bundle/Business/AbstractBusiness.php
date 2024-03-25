<?php
/**
 * PhpStorm.
 * User: Jay
 * Date: 2019/7/24
 */

namespace PHPZlc\PHPZlc\Bundle\Business;


use Doctrine\DBAL\Connection;
use Psr\Container\ContainerInterface;
use PHPZlc\PHPZlc\Abnormal\Errors;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class AbstractBusiness extends AbstractController
{
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
        $this->em = $this->getDoctrine()->getManager();
        $this->conn = $this->getDoctrine()->getConnection();

        if(empty(self::$validation)){
            self::$validation =  Validation::createValidatorBuilder()
                ->enableAnnotationMapping()
                ->getValidator();
        }
    }

    protected function getParameter(string $name)
    {
        if($this->container->has('parameter_bag')){
            return parent::getParameter($name);
        }

        return ActionLoad::$globalParameter->get($name);
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
