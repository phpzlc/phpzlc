<?php

namespace PHPZlc\Kernel\Abnormal;

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
    public function __construct($msg, $code = 1, $name = '', $value = '', $group = '', $other = array())
    {
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
            $masInfo['name'] = $this->name;
        }

        return $masInfo;
    }
}