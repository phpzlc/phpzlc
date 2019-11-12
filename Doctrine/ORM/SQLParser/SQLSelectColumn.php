<?php
/**
 * PhpStorm.
 * User: Jay
 * Date: 2019/8/20
 */

namespace PHPZlc\Kernel\Doctrine\ORM\SQLParser;

use PHPZlc\Kernel\Doctrine\ORM\Untils\SQLHandle;

class SQLSelectColumn
{
    /**
     * @var string 字段名
     */
    public $name;

    /**
     * @var string sql语句部分
     */
    public $cloumn;

    /**
     * @var boolean
     */
    public $isAs;

    /**
     * @var array $containCloumns
     */
    public $containCloumns = [];

    /**
     * @var boolean
     */
    public $isField = false;

    /**
     * @var string
     */
    public $fieldPre = null;

    /**
     * @var string
     */
    public $fieldName = null;


    public function parser()
    {
        $this->containCloumns = SQLHandle::searchField($this->cloumn);

        if(count($this->containCloumns) == 1 && array_key_exists($this->cloumn, $this->containCloumns)){
            $this->isField = true;
            $this->fieldPre = $this->containCloumns[$this->cloumn]['pre'];
            $this->fieldName = $this->containCloumns[$this->cloumn]['column'];
            if(!$this->isAs) {
                $this->name = $this->containCloumns[$this->cloumn]['column'];
            }
        }
    }
}