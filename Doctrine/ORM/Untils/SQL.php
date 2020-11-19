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
    public function simple_array_in($value , $column)
    {
        $vs = explode(',', $value);
        $sql_array = [];

        foreach ($vs as $value) {
            $sql_array[] = " CONCAT(',', $column, ',')  LIKE '%$value%' ";
        }

        $sql = '(' . implode(' OR ', $sql_array) . ')';

        return $sql;
    }

    /**
     * 过滤
     *
     * @param $param
     * @return mixed
     */
    public function filter($param)
    {
        return $param;
    }

    /**
     * sql参数拼接
     *
     * @param $sql
     * @param array $params
     * @return string
     */
    public function mosaic($sql, $params = array())
    {
        $sql_array = str_split($sql);

        $i = 0;
        foreach ($sql_array as $index => $value)
        {
            if($i < count($params)){
                if($value == '?'){
                    $sql_array[$index] = "'{$params[$i]}'";
                    $i++;
                }
            }
        }

        $sql = '';
        foreach ($sql_array as $value){
            $sql .= $value;
        }

        return $sql;
    }
}