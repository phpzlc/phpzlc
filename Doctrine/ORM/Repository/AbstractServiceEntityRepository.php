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
     * 数据是否存在
     *
     * @param Rules|array|null $rules
     * @param ResultSetMappingBuilder|null $resultSetMappingBuilder
     * @param string $aliasChain
     * @return boolean
     */
    final public function isExist($rules = null, ResultSetMappingBuilder $resultSetMappingBuilder = null, $aliasChain = '')
    {
        $this->rules($rules, $resultSetMappingBuilder, $aliasChain);
        $this->sqlArray['select'] = '1';

        return $this->_em->getConnection()->fetchOne($this->getSql()) == '1' ? true : false;
    }


    /**
     * 在全部数据中检索
     *
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
     * 在分页数据中检索
     *
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
     * 检索一条数据
     *
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
     * 检索得到最新的一条数据
     *
     * @param Rules|array|null $rules
     * @param ResultSetMappingBuilder|null $resultSetMappingBuilder
     * @param string $aliasChain
     * @return mixed|null
     */
    final public function findLastAssoc($rules = null, ResultSetMappingBuilder $resultSetMappingBuilder = null, $aliasChain = '')
    {
        $this->rules($rules, $resultSetMappingBuilder, $aliasChain);
        $this->sqlArray['orderBy'] = 'ORDER BY '. "sql_pre.{$this->getPrimaryKey()} DESC";
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
     * 根据id检索一条数据
     *
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
     * 得到数据总数
     *
     * @param Rules|array|null $rules
     * @param ResultSetMappingBuilder|null $resultSetMappingBuilder
     * @param string $aliasChain
     * @return array|mixed
     */
    final public function findCount($rules = null, ResultSetMappingBuilder $resultSetMappingBuilder = null, $aliasChain = '')
    {
        $this->rules($rules, $resultSetMappingBuilder, $aliasChain);
        $this->sqlArray['select'] = 'count(sql_pre.' . $this->getPrimaryKey() .')';
        $this->sqlArray['orderBy'] = '';
        if(!empty($this->sqlArray['groupBy'])){
            $this->sqlArray['groupBy'] = str_replace(' GROUP BY ', '', $this->sqlArray['groupBy']);
            $this->sqlArray['select'] = 'count(distinct '. $this->sqlArray['groupBy'] .')';
            $this->sqlArray['orderBy'] = '';
            $this->sqlArray['groupBy'] = '';
        }

        return $this->_em->getConnection()->fetchOne($this->getSql());
    }

    /**
     * 得到指定字段
     *
     * @param $column
     * @param $rules
     * @param ResultSetMappingBuilder|null $resultSetMappingBuilder
     * @param $aliasChain
     * @return false|mixed
     * @throws \Doctrine\DBAL\Exception
     */
    final public function findColumn($column, $rules = null, ResultSetMappingBuilder $resultSetMappingBuilder = null, $aliasChain = '')
    {
        $this->rules($rules, $resultSetMappingBuilder, $aliasChain);
        $this->sqlArray['select'] = $column;
        $this->sqlArray['orderBy'] = '';

        return $this->_em->getConnection()->fetchOne($this->getSql());
    }

    /**
     * 得到检索sql
     *
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

    final public function findOneBy(array $criteria, array $orderBy = null)
    {
        return $this->findAssoc($criteria);
    }

    final public function find($id, $lockMode = null, $lockVersion = null)
    {
        if(is_array($id) || is_object($id)){
            return $this->findAssoc($id);
        }else{
            if(is_array($lockMode) || is_object($lockMode)){
                return $this->findAssocById($id, $lockMode);
            }else{
                return $this->findAssocById($id);
            }
        }
    }
}
