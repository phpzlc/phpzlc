<?php
/**
 * 拆表管理与操作
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

abstract class AbstractDismantleTableRepository extends AbstractServiceEntityRepository
{
    /**
     * @var string 源(参照)表名称
     */
    public $referTableName;

    /**
     * @var string 拆表标记
     */
    public $dismantleMark;

    public function __construct(ManagerRegistry $registry, string $entityClass)
    {
        parent::__construct($registry, $entityClass);
        $this->referTableName = $this->getTableName();
    }

    /**
     * 设置拆表标识
     *
     * @param $dismantleMark
     * @return $this
     */
    public function dismantleMark($dismantleMark)
    {
        $this->dismantleMark = $dismantleMark;
        parent::setTableName($this->getDismantleTableName($this->getTableName(), $dismantleMark));

        return $this;
    }

    /**
     * 得到拆分表的名称
     *
     * @param $tableName
     * @param $dismantleMark
     * @return string
     */
    private function getDismantleTableName($tableName, $dismantleMark)
    {
        if(empty($dismantleMark)){
            die('拆表标识不能为空');
        }

        if(strpos($tableName, '_dis_') == false){
            return $tableName . '_dis_' . md5($dismantleMark);
        }else{
            return $tableName;
        }
    }

    /**
     * 得到全部的拆分表
     *
     * @return array
     */
    function getAllDismantleTable()
    {
        $all = $this->_em->getConnection()->fetchAll("SHOW TABLES LIKE '{$this->referTableName}_dis_%'");

        $tables = [];

        foreach ($all as $item) {
            foreach ($item as $table){
                $tables[] = $table;
            }
        }

        return $tables;
    }

    /**
     * 创建拆分表
     */
    public function createDismantleTable()
    {
        $schemaManager = $this->_em->getConnection()->getSchemaManager();

        if (!$schemaManager->tablesExist(array($this->getTableName()))) {
            $schemaTool = new \Doctrine\ORM\Tools\SchemaTool($this->_em);
            $schemaTool->createSchema(array($this->getClassMetadata()));
        }

        return $this;
    }

    /**
     * 更新全部拆分表的结构
     */
    public function updateAllDismantleTable()
    {
        $dismantleTables = $this->getAllDismantleTable();
        
        foreach ($dismantleTables as $dismantleTable) {
            $this->setTableName($dismantleTable);
            $schemaTool = new \Doctrine\ORM\Tools\SchemaTool($this->_em);
            $schemaTool->updateSchema(array($this->getClassMetadata()), true);
        }
    }
}
