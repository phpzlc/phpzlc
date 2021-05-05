<?php
/**
 * PhpStorm.
 * User: Jay
 * Date: 2021/5/5
 */

namespace PHPZlc\PHPZlc\Composer;

use Composer\Script\Event;
use Composer\Installer\PackageEvent;

class Script
{
    /**
     * 安装之后执行
     */
    public function postInstallCmd()
    {
        $configs = [
            "Doctrine/ORM/Rewrite/Templates/Repository.tpl.php" => "vendor/symfony/maker-bundle/src/Resources/skeleton/doctrine/Repository.tpl.php",
            "Doctrine/ORM/Rewrite/Hydration/ObjectHydrator.php"=> "vendor/doctrine/orm/lib/Doctrine/ORM/Internal/Hydration/ObjectHydrator.php",
            "Doctrine/ORM/Rewrite/MakeEntityRegenerate/ClassSourceManipulator.php" => "vendor/symfony/maker-bundle/src/Util/ClassSourceManipulator.php",
            "Doctrine/ORM/Rewrite/MakeEntityRegenerate/EntityRegenerator.php" => "vendor/symfony/maker-bundle/src/Doctrine/EntityRegenerator.php"
        ];

        foreach ($configs as $key => $value){
            $this->tihuan($key, $value);
        }

        echo 0;
    }

    /**
     * 替换函数
     *
     * @param $source
     * @param $dest
     */
    private function tihuan($source, $dest)
    {
        $vender_path = dirname(dirname(dirname(dirname(__DIR__))));
        $phpzlc = dirname(dirname(__DIR__));

        echo $vender_path;
        echo $phpzlc;
        exit;

        unlink($vender_path . DIRECTORY_SEPARATOR . $dest);
        copy($phpzlc . DIRECTORY_SEPARATOR . $source, $vender_path . DIRECTORY_SEPARATOR . $dest);
    }
}