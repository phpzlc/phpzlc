<?php
/**
 * PhpStorm.
 * User: Jay
 * Date: 2019/7/24
 */

namespace PHPZlc\PHPZlc\Bundle\Business;


use Doctrine\DBAL\Connection;
use PHPZlc\PHPZlc\Bundle\Service\Log\Log;
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
        if(Errors::isExistError()){
            return false;
        }

        $errors = self::$validation->validate($class);

        if(count($errors) > 0){
            Errors::setError(new Error($errors->get(0)->getMessage(), 1, $errors->get(0)->getPropertyPath(), $errors->get(0)->getInvalidValue()));
            return false;
        }

        return true;
    }

    /**
     * 网络错误服务
     *
     * @param \Exception $exception
     */
    final protected function networkError(\Exception $exception)
    {
        if(!Errors::isExistError()) {
            if($_ENV['APP_ENV'] == 'dev'){
                throw $exception;
            }

            Errors::setErrorMessage('系统繁忙,请稍后再试');

            //记录错误日志
            Log::writeLog(
                ' [EXCEPTION_MESSAGE] ' . $exception->getMessage() .
                ' [ EXCEPTION_FILE ] ' . $exception->getFile() .
                ' [ EXCEPTION_CODE ] ' . $exception->getCode() .
                ' [ EXCEPTION_LINE ] '. $exception->getLine() .
                ' [ ERROR ] ' . Errors::getError()->msg
            );
        }
    }
}