<?php
/**
 * PhpStorm.
 * User: Jay
 * Date: 2019/8/20
 */

namespace PHPZlc\Kernel\Doctrine\ORM\SQLParser;

use Doctrine\ORM\QueryBuilder;
use PhpMyAdmin\SqlParser\Parser;
use PhpMyAdmin\SqlParser\Statements\SelectStatement;
use PhpMyAdmin\SqlParser\Utils\Query;
use PHPSQLParser\PHPSQLParser;
use PHPZlc\Kernel\Abnormal\PHPZlcException;
use PHPZlc\Kernel\Doctrine\ORM\Untils\SQLHandle;

class SQLParser
{
    public $sql;

    /**
     * @var SQLSelectColumn[]
     */
    public $selectColumns = [];

    /**
     * @var SQLSelectColumn[]
     */
    public $selectColumnsOfColumn = [];

    /**
     * @var array
     */
    public $alias = [];

    /**
     * @var array 使用到的字段
     */
    public $useFields = [];

    public function __construct($sql)
    {
        try {
            $this->sql = $sql;
            $this->useFields = SQLHandle::searchField($sql);
            $this->alias = array_flip(array_column($this->useFields, 'pre'));

            $parsed = new Parser($this->sql, false);

            $flages = Query::getFlags($parsed->statements[0], false);

            if($flages['is_select'] == true){
                foreach ($parsed->statements[0]->expr as $column){
                    $selectColumn = new SQLSelectColumn();
                    if($column->alias == null){
                        $selectColumn->isAs = false;
                        $selectColumn->name = $column->expr;
                    }else{
                        $selectColumn->isAs = true;
                        $selectColumn->name = $column->alias;
                    }

                    $selectColumn->cloumn = $column->expr;

                    $selectColumn->parser();

                    $this->selectColumns[$selectColumn->name] = $selectColumn;
                    $this->selectColumnsOfColumn[$selectColumn->cloumn] = $selectColumn;
                }
            }else{
                throw new PHPZlcException('SQL语句需为查询语句');
            }
        }catch (\Exception $exception) {
            throw new PHPZlcException('['. $exception->getMessage() .']--SQL ['. $this->sql .']错误');
        }
    }

    /**
     * 得到查询语句中按照字段前缀分组的字段类
     *
     * @return array
     */
    public function getSelectColumnFieldsOFPreGrouping()
    {
        $selectColumns = [];
        foreach ($this->selectColumns as $SQLSelectColumn){
            if($SQLSelectColumn->isField){
                $selectColumns[$SQLSelectColumn->fieldPre][] = $SQLSelectColumn;
            }
        }

        return $selectColumns;
    }

    /**
     * 得到查询语句中按照字段前缀分组的使用字段
     *
     * @return array
     */
    public function getUseFieldsOFPreGrouping()
    {
        foreach ($this->useFields as $key => $field) {
            $fields[$field['pre']][$key] = $field;
        }

        return $fields;
    }


    public function addUseField($pre, $column)
    {
        $this->useFields[$pre. '.' . $column] = [
            'pre' => $pre,
            'column' => $column
        ];

        $this->alias[$pre] = $pre;
    }
}