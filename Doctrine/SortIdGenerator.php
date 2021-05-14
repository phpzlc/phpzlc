<?php
namespace PHPZlc\PHPZlc\Doctrine;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Id\AbstractIdGenerator;
use Ramsey\Uuid\Uuid;

class SortIdGenerator extends AbstractIdGenerator
{
    function generate(EntityManager $em, $entity)
    {
        $uuid = Uuid::uuid1()->toString();

        $uuid = explode('-', $uuid);

        $uuid = substr($uuid[0], -4) . '1' . $uuid[3] . substr($uuid[4], -4);

        return str_replace('.','',microtime(true)) . '-' . $uuid  . '-' . rand(1,  100);
    }

}
