<?php
/**
 * PhpStorm.
 * User: Jay
 * Date: 2019/8/2
 */

namespace PHPZlc\PHPZlc\Doctrine\ORM\RuleColumn;

use ContainerZx2sALA\srcApp_KernelDevDebugContainer;
use PHPZlc\PHPZlc\Abnormal\PHPZlcException;
use PHPZlc\PHPZlc\Doctrine\ORM\Rule\Rule;
use PHPZlc\PHPZlc\Doctrine\ORM\Rule\Rules;

class RuleColumn
{
    /**
     * 字段类型 表格内
     */
    const PT_TABLE_IN  = 1;

    /**
     * 字段类型 表格外
     */
    const PT_TABLE_OUT = 2;

    /**
     * 字段类型 表格关联
     */
    const PT_TYPE_TARGET = 3;

    /**
     * @var string 字段名
     */
    public $name;

    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $propertyType;

    /**
     * @var string
     */
    public $propertyName;

    /**
     * @var string
     */
    public $sql;

    /**
     * @var string 描述
     */
    public $comment;

    /**
     * @var boolean
     */
    public $isEntity;

    /**
     * @var string
     */
    public $targetEntity;

    /**
     * @var string
     */
    public $sourceEntity;


    /**
     * @var string
     */
    public $targetName;


    /**
     * @var Rules
     */
    public $rules;

    /**
     * OuterColumn constructor.
     *
     * @param string $name  字段名
     * @param string $propertyName 属性名
     * @param string $comment 描述
     * @param string $type 类型
     * @param string $propertyType 字段类型
     * @param string $selectSql  查询SQL
     * @param string|null $whereSql WHERE SQL 不填则自动生成
     * @param string|null $orderBySql WHERE SQL 不填则自动生成
     * @param boolean $isEntity  是否为Entity
     * @param string|null $targetEntity  isEntity为true必填  关联至EntutyName
     * @param string| null $sourceEntity isEntity为true必填  由EntutyName关联
     * @param string|null $targetName isEntity为true必填 关联至EntutyName的字段名
     */
    public function __construct($name, $propertyName, $comment, $type, $propertyType, $sql, $isEntity = false, $targetEntity = null, $sourceEntity = null, $targetName = null)
    {
        if($propertyName == ''){
            throw new PHPZlcException('属性名不能为空');
        }

        if($sql == ''){
            throw new PHPZlcException('字段' . $propertyName . '[sql]不能为空');
        }

        if(empty($name)){
            $this->name = $propertyName;
        }

        if(empty($type)){
            $type = 'string';
        }

        if(empty($comment)){
            $comment = $this->comment;
        }

        $this->name = $name;
        $this->propertyName = $propertyName;
        $this->comment = $comment;
        $this->type = $type;
        $this->propertyType = $propertyType;
        $this->sql = $sql;
        $this->isEntity = $isEntity;
        $this->targetEntity = $targetEntity;
        $this->sourceEntity = $sourceEntity;
        $this->targetName = $targetName;
        $this->rules = new Rules();
    }

    /**
     * @param Rule $rule
     */
    public function addRule(Rule $rule)
    {
        $this->rules->addRule($rule);
    }

    public function getSql($alias = [])
    {
        return $this->sqlProcess($this->sql, $alias);
    }

    public function getSqlComment($alias)
    {
        return $alias . '.' . $this->name;
    }


    private function sqlProcess($sql , $alias)
    {
        foreach ($alias as $key => $value){
            if($key != $value){
                $sql = str_replace($key . '.', $value . '.', $sql);
            }
        }

        return $sql;
    }


}