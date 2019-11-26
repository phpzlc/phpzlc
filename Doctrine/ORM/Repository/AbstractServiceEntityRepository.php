<?php
/**
 * PhpStorm.
 * User: Jay
 * Date: 2019/7/18
 */

namespace PHPZlc\PHPZlc\Doctrine\ORM\Repository;

use App\Entity\AuthToken;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr\Select;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use PhpMyAdmin\SqlParser\Parser;
use PHPSQLParser\PHPSQLParser;
use PHPZlc\PHPZlc\Abnormal\PHPZlcException;
use PHPZlc\PHPZlc\Doctrine\ORM\Repository\OtherField\Field;
use PHPZlc\PHPZlc\Doctrine\ORM\Rule\Rule;
use PHPZlc\PHPZlc\Doctrine\ORM\Rule\Rules;
use PHPZlc\PHPZlc\Doctrine\ORM\RuleColumn\ClassRuleMetaData;
use PHPZlc\PHPZlc\Doctrine\ORM\RuleColumn\ClassRuleMetaDataFactroy;
use PHPZlc\PHPZlc\Doctrine\ORM\RuleColumn\RuleColumn;
use PHPZlc\PHPZlc\Doctrine\ORM\SQLParser\SQLParser;
use PHPZlc\PHPZlc\Doctrine\ORM\SQLParser\SQLSelectColumn;
use PHPZlc\Validate\Validate;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;

abstract class AbstractServiceEntityRepository extends  AbstractServiceRuleRepository
{

#################################  查询 start ##################################

    public function findAll(Rules $rules = null, ResultSetMappingBuilder $resultSetMappingBuilder = null, $aliasChain = '')
    {
        $this->rules($rules, $resultSetMappingBuilder, $aliasChain);
        $this->getSql();
        $query = $this->_em->createNativeQuery($this->sql, $this->runResultSetMappingBuilder);

        return $query->getResult();
    }

    public function findLatestOne(Rules $rules = null, ResultSetMappingBuilder $resultSetMappingBuilder = null, $aliasChain = '')
    {
        $this->rules($rules, $resultSetMappingBuilder, $aliasChain);
        $this->getSql();
        $query = $this->_em->createNativeQuery($this->sql . ' LIMIT 1', $this->runResultSetMappingBuilder);

        $result = $query->getResult();

        if(!empty($result)){
            $result = $result[0];
        }

        return $result;
    }

    public function findLimit($rows, $page = 1, Rules $rules = null, ResultSetMappingBuilder $resultSetMappingBuilder = null, $aliasChain = '')
    {
        $this->rules($rules, $resultSetMappingBuilder, $aliasChain);
        $this->getSql();
        $query = $this->_em->createNativeQuery($this->sql . " LIMIT " . ($page - 1) . ", {$rows}", $this->runResultSetMappingBuilder);

        return $query->getResult();
    }

    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        return parent::findBy($criteria, $orderBy, $limit, $offset);
    }

    public function find($id, $lockMode = null, $lockVersion = null)
    {
        return parent::find($id, $lockMode, $lockVersion);
    }

    public function findOneBy(array $criteria, array $orderBy = null)
    {
        return parent::findOneBy($criteria, $orderBy);
    }


#################################   测试 start ##################################


    public function test()
    {
        $resultSetMappingBuilder = new ResultSetMappingBuilder($this->getEntityManager());

        $rules = new Rules();
        $rules->addRule(new Rule(Rule::R_SELECT, 'sql_pre.*, at.password, sql_pre.cc'));
        $rules->addRule(new Rule('at.password', '12'));
        $rules->addRule(new Rule('authToken_join', ['alias' => 'at']));
        $this->rules($rules, $resultSetMappingBuilder);
        dump($rules);
        dump($this->runRules, 'run');
        $this->getSql();
        echo $this->sql;
        $query = $this->_em->createNativeQuery($this->sql, $this->runResultSetMappingBuilder);
        dump($query->getResult());
        dump($this->getEntityManager()->getConnection()->fetchAll($this->sql));
    }
}