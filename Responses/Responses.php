<?php

/**
 * 响应封装
 */

namespace PHPZlc\PHPZlc\Responses;

use PHPZlc\PHPZlc\Abnormal\Error;
use PHPZlc\PHPZlc\Abnormal\PHPZlcException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class Responses
{
    /**
     * 返回数据格式json
     */
    const RESPONSE_JSON = 'json';

    const RESPONSE_DEBUG = 'debug';

    public static function success($message, $data = [], $code = 0, $type = self::RESPONSE_JSON)
    {
        return static::value($code, $message, [], $data, $type);
    }

    public static function error($error, $code = 1, $data = [], $type = self::RESPONSE_JSON)
    {
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
            'data' => $data
        );

        if($type == self::RESPONSE_JSON){
            return new JsonResponse($result);
        }

        dump($result);exit;
    }
}
