<?php
/**
 * PhpStorm.
 * User: Jay
 * Date: 2018/5/2
 */

namespace PHPZlc\PHPZlc\Bundle\Command;


use MongoDB\Driver\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class InstallCommand extends Base
{
    private $configs = [
        "phpzlc/phpzlc/Doctrine/ORM/Rewrite/Templates/Repository.tpl.php" => "symfony/maker-bundle/src/Resources/skeleton/doctrine/Repository.tpl.php",
        "phpzlc/phpzlc/Doctrine/ORM/Rewrite/Hydration/ObjectHydrator.php"=> "doctrine/orm/lib/Doctrine/ORM/Internal/Hydration/ObjectHydrator.php",
        "phpzlc/phpzlc/Doctrine/ORM/Rewrite/MakeEntityRegenerate/ClassSourceManipulator.php" => "symfony/maker-bundle/src/Util/ClassSourceManipulator.php",
        "phpzlc/phpzlc/Doctrine/ORM/Rewrite/MakeEntityRegenerate/EntityRegenerator.php" => "symfony/maker-bundle/src/Doctrine/EntityRegenerator.php"
    ];

    public function configure()
    {
        $this
            ->setName($this->command_pre . 'install')
            ->setDescription($this->description_pre . '安装');
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $filesystem = new Filesystem();

        foreach ($this->configs as $originFile => $targetFile)
        {
            $originFile = $this->getRootPath() . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR. $originFile;
            $targetFile = $this->getRootPath() . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR. $targetFile;
            
            $filesystem->copy($originFile, $targetFile);

            $this->io->success('内核拷贝'. $originFile . '=>' . $targetFile );

        }

        $this->io->success('安装成功');

        return 0;
    }
}

