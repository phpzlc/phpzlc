<?php
/**
 * PhpStorm.
 * User: Jay
 * Date: 2019/8/8
 */

namespace PHPZlc\PHPZlc\Doctrine\ORM\RuleColumn;

use Doctrine\ORM\Mapping\ClassMetadata;
use PHPZlc\PHPZlc\Abnormal\PHPZlcException;
use PHPZlc\PHPZlc\Doctrine\ORM\Rule\Rule;

class ClassRuleMetaData
{
    /**
     * @var ClassMetadata
     */
    private $_class;

    /**
     * @var RuleColumn[]
     */
    private $ruleColumns = [];

    /**
     * @var RuleColumn[]
     */
    private $ruleColumnOfNames = [];

    /**
     * @return ClassMetadata
     */
    public function getClassMetadata()
    {
        return $this->_class;
    }

    /**
     * @return RuleColumn[]
     */
    public function getAllRuleColumn()
    {
        return $this->ruleColumns;
    }

    /**
     * @param array $propertyTypes
     * @return array
     */
    public function getAllColumnName($propertyTypes = array(RuleColumn::PT_TABLE_IN, RuleColumn::PT_TYPE_TARGET))
    {
        foreach ($this->getAllRuleColumn() as $ruleColumn){
            if(empty($propertyTypes) || in_array($ruleColumn->propertyType, $propertyTypes)) {
                $allColumnName[] = $ruleColumn->name;
            }
        }

        return $allColumnName;
    }

    public function getSelectSql($propertyTypes = array(RuleColumn::PT_TABLE_IN, RuleColumn::PT_TYPE_TARGET), $pre = 'sql_pre')
    {
        $select = '';

        foreach ($this->getAllColumnName($propertyTypes) as $columnName){
            $select .= ', '. $pre . '.' . $columnName;
        }

        return ltrim($select, ',');
    }

    /**
     * @param $propertyName
     * @return string
     */
    public function getColumnNameOfPropertyName($propertyName)
    {
        return isset($this->ruleColumns[$propertyName]) ? $this->ruleColumns[$propertyName]->name : '';
    }

    /**
     * @param $columnName
     * @return string
     */
    public function getPropertyNameOfColumnName($columnName)
    {
        return isset($this->ruleColumnOfNames[$columnName]) ? $this->ruleColumnOfNames[$columnName]->propertyName : '';
    }

    /**
     * @param $propertyName
     * @return bool
     */
    public function hasRuleColumnOfPropertyName($propertyName)
    {
        return array_key_exists($propertyName, $this->ruleColumns);
    }

    /**
     * @param $column_name
     * @return bool
     */
    public function hasRuleColumnOfColumnName($column_name)
    {
        return array_key_exists($column_name, $this->ruleColumnOfNames);
    }

    /**
     * @param $propertyName
     * @return null|RuleColumn
     */
    public function getRuleColumnOfPropertyName($propertyName)
    {
        if($this->hasRuleColumnOfPropertyName($propertyName)){
            return $this->ruleColumns[$propertyName];
        }

        return null;
    }

    /**
     * @param $column_name
     * @return null|RuleColumn
     */
    public function getRuleColumnOfColumnName($column_name)
    {
        if($this->hasRuleColumnOfColumnName($column_name)){
            return $this->ruleColumnOfNames[$column_name];
        }

        return null;
    }


    /**
     * @param $ruleSuffixName
     * @param string $removeSuffix
     * @return null|RuleColumn
     */
    public function getRuleColumnOfRuleSuffixName($ruleSuffixName, $removeSuffix = '')
    {
        $name = str_replace($removeSuffix, '', $ruleSuffixName);

        if($this->hasRuleColumnOfColumnName($name)){
            return $this->getRuleColumnOfColumnName($name);
        }

        if($this->hasRuleColumnOfPropertyName($name)){
            return $this->getRuleColumnOfPropertyName($name);
        }

        return null;
    }

    /**
     * @param $fieldName
     * @return string
     */
    public function getCommentOfField($fieldName)
    {

        if(array_key_exists($fieldName, $this->_class->fieldMappings)){
            if(!empty($this->_class->fieldMappings[$fieldName]->options)){
                if(array_key_exists("comment", $this->_class->fieldMappings[$fieldName]->options)){
                    return $this->_class->fieldMappings[$fieldName]->options["comment"];
                }
            }
        }

        return '';
    }

    public function getNullableOfField($fieldName)
    {
        if(array_key_exists($fieldName, $this->_class->fieldMappings)){
            if(!empty($this->_class->fieldMappings[$fieldName]->nullable)){
                return true;
            }
        }
        
        return false;
    }

    public function __construct(ClassMetadata $classMetadata)
    {
        $this->_class = $classMetadata;

        //表普通字段
        foreach ($classMetadata->fieldNames as $name => $fieldName){
            $this->ruleColumns[$fieldName] = new RuleColumn(
                $name,
                $fieldName,
                $this->getCommentOfField($fieldName),
                $classMetadata->getTypeOfField($fieldName),
                RuleColumn::PT_TABLE_IN,
                'sql_pre.' . $name,
                $this->getNullableOfField($fieldName),
                false
            );
        }

        //表关联字段
        foreach ($classMetadata->associationMappings as $associationMapping => $mapping){
            if(isset($mapping['targetToSourceKeyColumns'])) {
                foreach ($mapping['targetToSourceKeyColumns'] as $targetName_var => $name_var) {
                    $name = $name_var;
                    $targetName = $targetName_var;
                }
                $this->ruleColumns[$associationMapping] = new RuleColumn(
                    $name,
                    $associationMapping,
                    '',
                    'Entity',
                    RuleColumn::PT_TYPE_TARGET,
                    'sql_pre.' . $name,
                    $this->getNullableOfField($fieldName),
                    true,
                    $mapping['targetEntity'],
                    $mapping['sourceEntity'],
                    $targetName_var
                );
            }
        }

        //表外字段
        foreach ($classMetadata->reflClass->getProperties() as  $reflectionProperty) {
            $attributes = $reflectionProperty->getAttributes(\PHPZlc\PHPZlc\Doctrine\ORM\Mapping\OuterColumn::class);
            foreach ($attributes as $attribute) {
                $name = isset($attribute->getArguments()['name']) ? $attribute->getArguments()['name'] : '';
                $comment = isset($attribute->getArguments()['comment']) ? $attribute->getArguments()['comment'] : '';
                $type = isset($attribute->getArguments()['type']) ? $attribute->getArguments()['type'] : '';
                $sql = isset($attribute->getArguments()['sql']) ? $attribute->getArguments()['sql'] : '';
                $this->ruleColumns[$reflectionProperty->getName()] = new RuleColumn(
                    empty($name) ? $reflectionProperty->getName() : $name,
                    $reflectionProperty->getName(),
                    $comment,
                   $type,
                    RuleColumn::PT_TABLE_OUT,
                    $sql,
                    false,
                    false
                );
            }

            $attributes = $reflectionProperty->getAttributes(\PHPZlc\PHPZlc\Doctrine\ORM\Mapping\AddRule::class);
            foreach ($attributes as $attribute) {
                $name = isset($attribute->getArguments()['name']) ? $attribute->getArguments()['name'] : '';
                $value = isset($attribute->getArguments()['value']) ? $attribute->getArguments()['value'] : '';
                $collision = isset($attribute->getArguments()['collision']) ? $attribute->getArguments()['collision'] : null;
                $jointClass = isset($attribute->getArguments()['jointClass']) ? $attribute->getArguments()['jointClass'] : null;
                $jointSort = isset($attribute->getArguments()['jointClass']) ? $attribute->getArguments()['jointSort'] : null;
                if (in_array($name, Rule::$defRule, true)) {
                    throw new PHPZlcException('设置规则不能为默认规则');
                }
                $this->ruleColumns[$reflectionProperty->getName()]->addRule(new Rule(
                    $name,
                    $value,
                    $collision,
                    $jointClass,
                    $jointSort,
                ));
            }
        }

        foreach ($this->ruleColumns as $column => $ruleColumn){
            $this->ruleColumnOfNames[$ruleColumn->name] = $ruleColumn;
        }
    }
}