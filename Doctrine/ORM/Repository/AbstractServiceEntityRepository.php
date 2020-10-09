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

    /**
     * @param Rules|array|null $rules
     * @param ResultSetMappingBuilder|null $resultSetMappingBuilder
     * @param string $aliasChain
     * @return array|mixed
     */
    final public function findAll($rules = null, ResultSetMappingBuilder $resultSetMappingBuilder = null, $aliasChain = '')
    {
        $this->rules($rules, $resultSetMappingBuilder, $aliasChain);
        $this->getSql();
        $query = $this->_em->createNativeQuery($this->sql, $this->runResultSetMappingBuilder);

        return $query->getResult();
    }

    /**
     * @param $rows
     * @param int $page
     * @param Rules|array|null $rules
     * @param ResultSetMappingBuilder|null $resultSetMappingBuilder
     * @param string $aliasChain
     * @return mixed
     */
    final public function findLimitAll($rows, $page = 1,$rules = null, ResultSetMappingBuilder $resultSetMappingBuilder = null, $aliasChain = '')
    {
        $this->rules($rules, $resultSetMappingBuilder, $aliasChain);

        $this->getSql();

        $query = $this->_em->createNativeQuery($this->sql . " LIMIT " . (($page - 1) * $rows) . ", {$rows}", $this->runResultSetMappingBuilder);

        return $query->getResult();
    }

    /**
     * @param Rules|array|null $rules
     * @param ResultSetMappingBuilder|null $resultSetMappingBuilder
     * @param string $aliasChain
     * @return mixed|null
     */
    final public function findAssoc($rules = null, ResultSetMappingBuilder $resultSetMappingBuilder = null, $aliasChain = '')
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

    /**
     * @param Rules|array|null $rules
     * @param ResultSetMappingBuilder|null $resultSetMappingBuilder
     * @param string $aliasChain
     * @return mixed|null
     */
    final public function findLastAssoc($rules = null, ResultSetMappingBuilder $resultSetMappingBuilder = null, $aliasChain = '')
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

    /**
     * @param $id
     * @param Rules|array|null $rules
     * @param ResultSetMappingBuilder|null $resultSetMappingBuilder
     * @param string $aliasChain
     * @return mixed|null
     */
    final public function findAssocById($id, $rules = null, ResultSetMappingBuilder $resultSetMappingBuilder = null, $aliasChain = '')
    {
        if(empty($rules)){
            $rules = new Rules();
        }elseif(is_array($rules)){
            $rules = new Rules($rules);
        }

        $rules->addRule(new Rule($this->getPrimaryKey(), $id));
        $this->rules($rules, $resultSetMappingBuilder, $aliasChain);
        $this->sqlArray['orderBy'] = '';
        $this->getSql();

        $query = $this->_em->createNativeQuery($this->sql, $this->runResultSetMappingBuilder);;

        $result = $query->getResult();

        if(!empty($result)){
            $result = $result[0];
        }else{
            $result = null;
        }

        return $result;
    }

    /**
     * @param Rules|array|null $rules
     * @param ResultSetMappingBuilder|null $resultSetMappingBuilder
     * @param string $aliasChain
     * @return array|mixed
     */
    final public function findCount($rules = null, ResultSetMappingBuilder $resultSetMappingBuilder = null, $aliasChain = '')
    {
        $this->rules($rules, $resultSetMappingBuilder, $aliasChain);
        $this->sqlArray['select'] = 'count(sql_pre.' . $this->getPrimaryKey() .')';

        return $this->_em->getConnection()->fetchColumn($this->getSql());
    }
    
    /**
     * @param Rules|array|null $rules
     * @param ResultSetMappingBuilder|null $resultSetMappingBuilder
     * @param string $aliasChain
     * @return string
     */
    final public function findSql($rules = null, ResultSetMappingBuilder $resultSetMappingBuilder = null, $aliasChain = '')
    {
        $this->rules($rules, $resultSetMappingBuilder, $aliasChain);

        return $this->getSql();
    }
}