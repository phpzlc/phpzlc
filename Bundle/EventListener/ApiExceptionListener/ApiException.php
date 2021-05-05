<?php
/**
 * PhpStorm.
 * User: Jay
 * Date: 2020/1/2
 */

namespace PHPZlc\PHPZlc\Bundle\EventListener\ApiExceptionListener;


use PHPZlc\PHPZlc\Abnormal\PHPZlcException;
use PHPZlc\PHPZlc\Responses\Responses;
use Throwable;

class ApiException extends PHPZlcException
{
    private $data;

    private $type;

    public function getData()
    {
        return $this->data;
    }

    public function getType()
    {
        return $this->type;
    }

    public function __construct($msg, $code = '$_ENV[API_ERROR_CODE]def(1)', $data = [], $type = Responses::RESPONSE_JSON, Throwable $previous = null)
    {
        if($code == '$_ENV[API_ERROR_CODE]def(1)'){
            $code = Responses::getEnvValue('API_ERROR_CODE', 1);
        }

        parent::__construct($msg, $code, $previous);

        $this->data = $data;
        $this->type = $type;
    }
}