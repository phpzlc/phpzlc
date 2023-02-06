<?php

namespace PHPZlc\PHPZlc\Abnormal;

class Error
{
    /**
     * @var string 错误码
     */
    public $code;

    /**
     * @var string 错误信息
     */
    public $msg;

    /**
     * @var string 错误名或标识
     */
    public $name;

    /**
     * @var void
     */
    public $value;

    /**
     * @var string 错误分组
     */
    public $group;

    /**
     * @var array  错误其他信息
     */
    public $other;

    /**
     * Error constructor.
     *
     * @param $msg
     * @param int $code
     * @param string $name
     * @param static $group
     * @param array $other
     */
    public function __construct($msg, $code = '$_ENV[API_ERROR_CODE]def(1)', $name = '', $value = '', $group = '', $other = array())
    {
        if(empty($code) || $code == '$_ENV[API_ERROR_CODE]def(1)'){
            $code =  array_key_exists('API_ERROR_CODE', $_ENV) ? $_ENV['API_ERROR_CODE'] : 1;
        }

        $this->code = $code;
        $this->msg = $msg;
        $this->name = $name;
        $this->value = $value;
        $this->group = $group;
        $this->other = $other;
    }


    public function getMsgInfo()
    {
        $masInfo = $this->other;
        if(!empty($this->name)) {
            $masInfo['keyValue'] = [
                "key" => $this->name,
                "value" => $this->value,
            ];
        }

        return $masInfo;
    }

    public function toArray()
    {
        return [
            'code' => $this->code,
            'msg' => $this->msg,
            'name' => $this->name,
            'value' => $this->value,
            'group' => $this->group,
            'other' => $this->other,
            'msgInfo' => $this->getMsgInfo()
        ];
    }
}