<?php
/**
 * PhpStorm.
 * User: Jay
 * Date: 2019/11/26
 */

namespace PHPZlc\PHPZlc\Doctrine\ORM\Untils;


class Str
{
    public static function asCamelCase(string $str): string
    {
        return strtr(ucwords(strtr($str, ['_' => ' ', '.' => ' ', '\\' => ' '])), [' ' => '']);
    }
}