<?php
/**
 * PhpStorm.
 * User: Jay
 * Date: 2019/7/24
 */

namespace PHPZlc\PHPZlc\Bundle\Business;


use Doctrine\DBAL\Connection;
use Psr\Container\ContainerInterface;
use PHPZlc\PHPZlc\Abnormal\Error;
use PHPZlc\PHPZlc\Abnormal\Errors;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\Validation;

abstract class AbstractBusiness extends AbstractController
{
    protected $em;

    /**
     * @var Connection
     */
    protected $conn;

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

    public function validator($class) : bool
    {
        $errors = self::$validation->validate($class);

        if(count($errors) > 0){
            Errors::setError(new Error($errors->get(0)->getMessage(), 1, $errors->get(0)->getPropertyPath(), $errors->get(0)->getInvalidValue()));
            return false;
        }

        return true;
    }
}