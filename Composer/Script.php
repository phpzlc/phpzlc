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
    public function postInstallCmd(Event $event)
    {
        $vender_dir = $event->getComposer()->getConfig()->get('vendor-dir');

        $configs = [
            "phpzlc/phpzlc/Doctrine/ORM/Rewrite/Templates/Repository.tpl.php" => "symfony/maker-bundle/src/Resources/skeleton/doctrine/Repository.tpl.php",
            "phpzlc/phpzlc/Doctrine/ORM/Rewrite/Hydration/ObjectHydrator.php"=> "doctrine/orm/lib/Doctrine/ORM/Internal/Hydration/ObjectHydrator.php",
            "phpzlc/phpzlc/Doctrine/ORM/Rewrite/MakeEntityRegenerate/ClassSourceManipulator.php" => "symfony/maker-bundle/src/Util/ClassSourceManipulator.php",
            "phpzlc/phpzlc/Doctrine/ORM/Rewrite/MakeEntityRegenerate/EntityRegenerator.php" => "symfony/maker-bundle/src/Doctrine/EntityRegenerator.php"
        ];

        foreach ($configs as $key => $value){
            $this->tihuan($vender_dir . $key , $vender_dir . $value);
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

        unlink($vender_path . DIRECTORY_SEPARATOR . $dest);
        copy($phpzlc . DIRECTORY_SEPARATOR . $source, $vender_path . DIRECTORY_SEPARATOR . $dest);
    }
}