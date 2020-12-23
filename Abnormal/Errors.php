<?php
namespace PHPZlc\PHPZlc\Abnormal;

use PHPZlc\PHPZlc\Bundle\Service\Log\Log;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Errors
{
    /**
     * @var Error[]
     */
    private static $errors = [];


    /**
     * 设置错误信息
     *
     * @param $msg
     */
    public static function setErrorMessage($msg)
    {
        static::setError(new Error($msg));
    }

    /**
     * 设置错误
     *
     * @param Error $error
     */
    public static function setError(Error $error)
    {
        static::$errors[] = $error;
    }

    /**
     * 得到错误
     *
     * @return bool|Error
     */
    public static function getError()
    {
        return empty(static::$errors) ? false : static::$errors[0];
    }


    /**
     * 是否存在错误
     *
     * @return bool
     */
    public static function isExistError()
    {
        return !empty(static::$errors);
    }

    /**
     * 得到全部错误
     *
     * @return Error[]
     */
    public static function getAllError()
    {
        return static::$errors;
    }

    /**
     * 覆盖错误
     *
     * @param Error $error
     */
    public static function coverError(Error $error)
    {
        array_unshift(static::$errors, $error);
    }

    /**
     * symfony ValidatorInterface class
     *
     * @param ValidatorInterface $validator
     * @param $class
     * @return bool
     */
    public static function validate(ValidatorInterface $validator, $class)
    {
        if(Errors::isExistError()){
            return false;
        }

        $errors = $validator->validate($class);

        if(count($errors) > 0){
            Errors::setError(new Error($errors->get(0)->getMessage(), 1, $errors->get(0)->getPropertyPath(), $errors->get(0)->getInvalidValue()));
            return false;
        }

        return true;
    }


    /**
     * 异常错误
     *
     * @param \Exception $exception
     * @return bool
     * @throws \Exception
     */
    public static function exceptionError(\Exception $exception)
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
        return false;
    }
}