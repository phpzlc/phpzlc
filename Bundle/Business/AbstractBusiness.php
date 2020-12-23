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
    protected $em;

    /**
     * @var Connection
     */
    protected $conn;

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

    public function validator($class) : bool
    {
        return Errors::validate(self::$validation, $class);
    }

    /**
     * 网络错误服务
     *
     * @param \Exception $exception
     */
    final protected function networkError(\Exception $exception)
    {
        return Errors::exceptionError($exception);
    }
}