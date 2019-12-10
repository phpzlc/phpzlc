<?php
/**
 * PhpStorm.
 * User: Jay
 * Date: 2019/7/18
 */

namespace PHPZlc\PHPZlc\Doctrine\ORM\Repository;

use Doctrine\ORM\Query\ResultSetMappingBuilder;
use PHPZlc\PHPZlc\Doctrine\ORM\Rule\Rule;
use PHPZlc\PHPZlc\Doctrine\ORM\Rule\Rules;

abstract class AbstractServiceEntityRepository extends  AbstractServiceRuleRepository
{

#################################  查询 start ##################################

    final public function findAll(Rules $rules = null, ResultSetMappingBuilder $resultSetMappingBuilder = null, $aliasChain = '')
    {
        $this->rules($rules, $resultSetMappingBuilder, $aliasChain);
        $this->getSql();
        $query = $this->_em->createNativeQuery($this->sql, $this->runResultSetMappingBuilder);

        return $query->getResult();
    }

    final public function findLimitAll($rows, $page = 1, Rules $rules = null, ResultSetMappingBuilder $resultSetMappingBuilder = null, $aliasChain = '')
    {
        $this->rules($rules, $resultSetMappingBuilder, $aliasChain);
        $this->getSql();
        $query = $this->_em->createNativeQuery($this->sql . " LIMIT " . ($page - 1) . ", {$rows}", $this->runResultSetMappingBuilder);

        return $query->getResult();
    }

    final public function findAssoc(Rules $rules = null, ResultSetMappingBuilder $resultSetMappingBuilder = null, $aliasChain = '')
    {
        $this->rules($rules, $resultSetMappingBuilder, $aliasChain);
        $this->getSql();
        $query = $this->_em->createNativeQuery($this->sql . ' LIMIT 1', $this->runResultSetMappingBuilder);

        $result = $query->getResult();

        if(!empty($result)){
            $result = $result[0];
        }else{
            $result = null;
        }

        return $result;
    }

    final public function findLastAssoc(Rules $rules = null, ResultSetMappingBuilder $resultSetMappingBuilder = null, $aliasChain = '')
    {
        $this->rules($rules, $resultSetMappingBuilder, $aliasChain);
        $this->sqlArray['orderBy'] = 'ORDER BY '. $this->sqlArray['finalOrderBy'];
        $this->getSql();

        $query = $this->_em->createNativeQuery($this->sql . ' LIMIT 1', $this->runResultSetMappingBuilder);

        $result = $query->getResult();

        if(!empty($result)){
            $result = $result[0];
        }else{
            $result = null;
        }

        return $result;
    }

    final public function findAssocById($id, Rules $rules = null, ResultSetMappingBuilder $resultSetMappingBuilder = null, $aliasChain = '')
    {
        $rules->addRule(new Rule($this->getPrimaryKey(), $id));
        $this->rules($rules, $resultSetMappingBuilder, $aliasChain);
        $this->sqlArray['orderBy'] = '';
        $this->getSql();

        $query = $this->_em->createNativeQuery($this->sql, $this->runResultSetMappingBuilder);

        $result = $query->getResult();

        if(!empty($result)){
            $result = $result[0];
        }elseif (count($result) > 1){
            throw new \Exception('findAssocById 查询值大于 1');
        }else{
            $result = null;
        }

        return $result;
    }
}