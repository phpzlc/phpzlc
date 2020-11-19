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

    private static function getEnvValue($key, $def)
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


    public static function exceptionError(\Exception $exception)
    {
        if($exception instanceof NotFoundHttpException) {
            throw new NotFoundHttpException();
        }

        $networkErrorMessage = static::getEnvValue('API_EXCEPTION_ERROR_MSG', '响应异常，服务发生错误');
        $netWorkErrorCode =  static::getEnvValue('API_EXCEPTION_ERROR_CODE', 500);

        if(Errors::isExistError()){
            $error = Errors::getError();
        }else{
            $error = new Error(
                $networkErrorMessage,
                $netWorkErrorCode,
                '',
                '',
                '',
                array(
                    '[EXCEPTION_MESSAGE]' =>  $exception->getMessage(),
                    '[EXCEPTION_DATETIME]' =>  date('Y-m-d H:i:s')
                )
            );

            //记录日志
            Log::writeLog(' [EXCEPTION_MESSAGE] ' .  $exception->getMessage() .
                ' [EXCEPTION_FILE] ' .  $exception->getFile() .
                ' [EXCEPTION_CODE] ' .  $exception->getCode() .
                ' [EXCEPTION_LINE] '.  $exception->getLine() .
                ' [ERROR] ' . $networkErrorMessage);
        }

        switch (SystemBaseController::getReturnType()) {
            case SystemBaseController::RETURN_SHOW_RESOURCE:
                throw new NotFoundHttpException($networkErrorMessage);
                break;
            case SystemBaseController::RETURN_HIDE_RESOURCE:
                return Responses::error($error);
            default:
                return new Response($networkErrorMessage, $netWorkErrorCode);
        }
    }

    private static function value($code, $msg, $msgInfo, $data, $type)
    {
        $result = array(
            'code' => $code,
            'msg' => $msg,
            'msgInfo' => $msgInfo,
            'data' => $data
        );

        if($type == self::RESPONSE_JSON){
            return new JsonResponse($result);
        }

        dump($result);exit;
    }
}
