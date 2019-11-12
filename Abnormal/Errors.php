<?php
namespace PHPZlc\Kernel\Abnormal;

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
}