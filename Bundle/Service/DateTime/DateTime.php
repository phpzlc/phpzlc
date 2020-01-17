<?php
/**
 * PhpStorm.
 * User: Jay
 * Date: 2020/1/10
 */
namespace PHPZlc\PHPZlc\Bundle\Service\DateTime;

class DateTime
{
    public static function date($format, $timestamp)
    {
        if(empty($timestamp)){
            return '';
        }else{
            return date($format, $timestamp);
        }
    }
}