<?php
/**
 * PhpStorm.
 * User: Jay
 * Date: 2019/8/21
 */

namespace PHPZlc\PHPZlc\Doctrine\ORM\Untils;

class SQLHandle
{
    /**
     * 解析SQL中的字段SQL 解析目标为 *.* 例如 sql_pre.name
     *
     * @param $sql
     * @return array
     */
    public static function searchField($sql)
    {
        $arr = [];

        $matches = [];
        preg_match_all("/[^\s,，（(]*\.[^\s,，)）]*/", $sql, $matches);

        foreach ($matches[0] as $match){
            $matchs = explode('.', $match);
            $count = count($matchs);
            if($count == 2){
                if($matchs[0] != '') {
                    $arr[$match] = [
                        'pre' => $matchs[0],
                        'column' => $matchs[1]
                    ];
                }
            }
        }

        return $arr;
    }
}