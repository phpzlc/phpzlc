<?php
/**
 * PhpStorm.
 * User: Jay
 * Date: 2020/11/11
 */

namespace PHPZlc\PHPZlc\Doctrine\ORM\Untils;


class SQL
{
    public static function in($string)
    {
        if(is_array($string)){
            $string = implode(',', $string);
        }

        $string = trim($string, ',');
        $string = str_replace('"','', $string);
        $string = str_replace("'",'', $string);

        if(empty($string)){
            return '';
        }

        $string = '"' . $string;
        $string = str_replace(",", '","', $string);
        $string  .= '"';

        return $string;
    }

    /**
     * 实现simple_array的in查询策略
     *
     * @param $value  1,2
     * @param $column
     * @return string
     */
    public static function simpleArrayIn($value , $column)
    {
        $vs = explode(',', $value);
        $sql_array = [];

        foreach ($vs as $value) {
            $sql_array[] = " CONCAT(',', $column, ',')  LIKE '%,$value,%' ";
        }

        $sql = '(' . implode(' OR ', $sql_array) . ')';

        return $sql;
    }
    
    /**
     * sql_pre 替换
     *
     * @param $sql
     * @param $pre
     * @return string
     */
    public static function sqlPreReplace($sql , $pre)
    {
        return str_replace('sql_pre.', $pre . '.', $sql);
    }
}
