<?php
/**
 * PhpStorm.
 * User: Jay
 * Date: 2019/8/9
 */

namespace PHPZlc\PHPZlc\Doctrine\ORM\RuleColumn;

use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\ClassMetadataFactory;

class ClassRuleMetaDataFactroy
{
    /**
     * @var ClassRuleMetaData[]
     */
    private static $classRuleMetadata = [];

    /**
     * @param ClassMetadata $classMetadata
     * @return ClassRuleMetaData
     */
    public static function getClassRuleMetadata(ClassMetadata $classMetadata)
    {
        if(self::hastClassRuleMetadata($classMetadata)){
            return self::$classRuleMetadata[$classMetadata->getName()];
        }else{
            $classRuleMetadata = new ClassRuleMetaData($classMetadata);
            self::$classRuleMetadata[$classMetadata->getName()] = $classRuleMetadata;

            return $classRuleMetadata;
        }
    }

    /**
     * @param ClassMetadata $classMetadata
     * @return bool
     */
    public static function hastClassRuleMetadata(ClassMetadata $classMetadata)
    {
        return array_key_exists($classMetadata->getName(), self::$classRuleMetadata);
    }

    /**
     * @return ClassRuleMetaData[]
     */
    public static function getAllClassRuleMetadata()
    {
        return self::$classRuleMetadata;
    }
}