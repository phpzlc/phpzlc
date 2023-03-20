<?php

/**
 * 响应封装
 */

namespace PHPZlc\PHPZlc\Responses;

use PHPZlc\PHPZlc\Abnormal\Error;
use PHPZlc\PHPZlc\Abnormal\Errors;
use PHPZlc\PHPZlc\Abnormal\PHPZlcException;
use PHPZlc\PHPZlc\Bundle\Controller\SystemBaseController;
use PHPZlc\PHPZlc\Bundle\Service\Log\Log;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Responses
{
    /**
     * 返回数据格式json
     */
    const RESPONSE_JSON = 'json';

    const RESPONSE_DEBUG = 'debug';

    /**
     * 全局返回参数
     *
     * @var array
     */
    private static $global = [];

    public static function addGlobalData($key, $data)
    {
        self::$global[$key] = $data;
    }

    public static function getGlobalData($key, $def = null)
    {
        if(array_key_exists($key, self::$global)){
            return self::$global[$key];
        }else{
            return $def;
        }
    }

    public static function removeGlobalData($key)
    {
        if(array_key_exists($key, self::$global)){
            unset($key);
        }
    }

    public static function getEnvValue($key, $def)
    {
        return array_key_exists($key, $_ENV) ? $_ENV[$key] : $def;
    }

    public static function success($message, $data = [], $code = '$_ENV[API_SUCCESS_CODE]def(0)', $type = '$_ENV[API_RESPONSE_TYPE]def(json)')
    {
        if($code == '$_ENV[API_SUCCESS_CODE]def(0)'){
            $code = static::getEnvValue('API_SUCCESS_CODE', 0);
        }
        if($type == '$_ENV[API_RESPONSE_TYPE]def(json)'){
            $type = static::getEnvValue('API_RESPONSE_TYPE', static::RESPONSE_JSON);
        }

        return static::value($code, $message, [], $data, $type);
    }

    public static function error($error, $code = '$_ENV[API_ERROR_CODE]def(1)', $data = [], $type = '$_ENV[API_RESPONSE_TYPE]def(json)')
    {
        if($code == '$_ENV[API_ERROR_CODE]def(1)'){
            $code = static::getEnvValue('API_ERROR_CODE', 1);
        }

        if($type == '$_ENV[API_RESPONSE_TYPE]def(json)'){
            $type = static::getEnvValue('API_RESPONSE_TYPE', static::RESPONSE_JSON);
        }

        if(is_string($error)){
            $error = new Error($error, $code);
        }

        return static::value($error->code, $error->msg, $error->getMsgInfo(), $data, $type);
    }

    private static function value($code, $msg, $msgInfo, $data, $type)
    {
        $result = array(
            'code' => $code,
            'msg' => $msg,
            'msgInfo' => $msgInfo,
            'data' => $data,
            'system' => self::$global
        );

        if($type == self::RESPONSE_JSON){
            return new JsonResponse($result);
        }

        dump($result);exit;
    }
}
