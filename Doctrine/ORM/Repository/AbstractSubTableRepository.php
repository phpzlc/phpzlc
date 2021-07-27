<?php
/**
 * 分表操作类
 *
 * User: Jay
 * Date: 2019/8/27
 */

namespace PHPZlc\PHPZlc\Doctrine\ORM\Repository;

use BaseBundle\Tools\Model\ModelPrimaryKeyAssociationAutoIncrement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr\Select;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use PHPZlc\Validate\Validate;
use Doctrine\Persistence\Mapping\ClassMetadata;
use PhpMyAdmin\SqlParser\Parser;

abstract class AbstractSubTableRepository extends AbstractServiceRuleRepository
{
    /**
     * @var string 源(参照)表名称
     */
    public $referTableName;

    /**
     * @var string 源(参照)表名称
     *
     */
    public $subMark;

    public function __construct(ManagerRegistry $registry, string $entityClass)
    {
        parent::__construct($registry, $entityClass);
        $this->referTableName = $this->getTableName();
    }

    /**
     * 设置分表标记
     *
     * @param $subMake
     * @return $this
     */
    public function subMake($subMake)
    {
        $this->subMark = $subMake;
        parent::setTableName($this->getSubTableName($this->getTableName(), $subMake));

        return $this;
    }

    /**
     * 得到分表的名字
     *
     * @param $tableName
     * @param $subMark
     * @return string
     */
    private function getSubTableName($tableName, $subMark)
    {
        return $tableName . '_sub_' . md5($subMark);
    }

    /**
     * 得到全部的分表
     *
     * @return array
     */
     function getAllSubTable()
     {
         $all = $this->_em->getConnection()->fetchAll("SHOW TABLES LIKE '{$this->referTableName}_sub_%'");

         $tables = [];

         foreach ($all as $item) {
             foreach ($item as $table){
                 $tables[] = $table;
             }
         }

         return $tables;
     }

    /**
     * 创建分表
     */
    public function createSubTable()
    {
        $schemaManager = $this->_em->getConnection()->getSchemaManager();

        if (!$schemaManager->tablesExist(array($this->getTableName()))) {
            $schemaTool = new \Doctrine\ORM\Tools\SchemaTool($this->_em);
            $schemaTool->createSchema(array($this->getClassMetadata()));
        }
        
        return $this;
    }

    /**
     * 更新全部分表结构
     */
    public function updateAllSubTable()
    {
        $subTables = $this->getAllSubTable();


        foreach ($subTables as $subTable) {
            $this->setTableName($subTable);
            $schemaTool = new \Doctrine\ORM\Tools\SchemaTool($this->_em);
            $schemaTool->updateSchema(array($this->getClassMetadata()), true);
        }
    }
}