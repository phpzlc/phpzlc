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

class PhpZlcApiException extends PHPZlcException
{
    private $data;

    private $type;

    public function getData()
    {
        return $this->type;
    }

    public function getType()
    {
        return $this->type;
    }

    public function __construct($msg, $code = 1, $data = [], $type = Responses::RESPONSE_JSON, Throwable $previous = null)
    {
        parent::__construct($msg, $code, $previous);

        $this->data = $data;
        $this->type = $type;
    }
}