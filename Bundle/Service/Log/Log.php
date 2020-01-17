<?php
/**
 * PhpStorm.
 * User: Jay
 * Date: 2020/1/3
 */
namespace PHPZlc\PHPZlc\Bundle\Service\Log;

use PHPZlc\PHPZlc\Bundle\Service\FileSystem\FileSystem;

class Log
{
    private static function getLogFilePath()
    {
        if(empty($logFileName)){
            $logFileName =  $_ENV['APP_ENV'] . '.log';
        }

        return dirname(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))))) . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR . $logFileName;
    }


    public static function writeLog($content, $logFileName = '')
    {
        if(!is_array($content)){
            $content = array($content);
        }

        foreach ($content as $index => $value){
            $content[$index] = sprintf('[%s]%s'. "\n", date('Y-m-d H:i:s'), $value);
        }


        file_put_contents(static::getLogFilePath($logFileName), $content, FILE_APPEND);
    }


    public static function readLog(int $rows = 20, string $logFileName = '')
    {
        $path = static::getLogFilePath($logFileName);

        if(!file_exists($path)){
            throw new NotFoundHttpException();
        }

        $FileSystem = new FileSystem();

        $return = $FileSystem->readFile($path, $rows);
        for ($i = count($return) - 1; $i > -1; $i --){
            echo $return[$i];
            echo '<br>';
        }
    }
}