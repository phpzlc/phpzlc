<?php
/**
 * PhpStorm.
 * User: Jay
 * Date: 2020/1/3
 */

namespace PHPZlc\PHPZlc\Bundle\Service\FileSystem;

use Psr\Container\ContainerInterface;

class FileSystem extends \Symfony\Component\Filesystem\Filesystem
{
    /**
     * 读取文件最后几行
     *
     * @param $file
     * @param $lines
     * @return array
     */
    public function readFile($file, $lines)
    {
        //global $fsize;
        $handle = fopen($file, "r");
        $linecounter = $lines;
        $pos = -2;
        $beginning = false;
        $text = array();
        while ($linecounter > 0) {
            $t = " ";
            while ($t != "\n") {
                if(fseek($handle, $pos, SEEK_END) == -1) {
                    $beginning = true;
                    break;
                }
                $t = fgetc($handle);
                $pos --;
            }
            $linecounter --;
            if ($beginning) {
                rewind($handle);
            }
            $text[$lines-$linecounter-1] = fgets($handle);
            if ($beginning) break;
        }

        fclose ($handle);

        return array_reverse($text);
    }
}