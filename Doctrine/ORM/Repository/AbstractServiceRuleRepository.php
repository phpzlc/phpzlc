<?php
/**
 * PhpStorm.
 * User: Jay
 * Date: 2019/8/27
 */

namespace PHPZlc\PHPZlc\Doctrine\ORM\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr\Select;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use PHPZlc\PHPZlc\Abnormal\PHPZlcException;
use PHPZlc\PHPZlc\Doctrine\ORM\Repository\OtherField\Field;
use PHPZlc\PHPZlc\Doctrine\ORM\Rule\Rule;
use PHPZlc\PHPZlc\Doctrine\ORM\Rule\Rules;
use PHPZlc\PHPZlc\Doctrine\ORM\RuleColumn\ClassRuleMetaData;
use PHPZlc\PHPZlc\Doctrine\ORM\RuleColumn\ClassRuleMetaDataFactroy;
use PHPZlc\PHPZlc\Doctrine\ORM\RuleColumn\RuleColumn;
use PHPZlc\PHPZlc\Doctrine\ORM\SQLParser\SQLParser;
use PHPZlc\PHPZlc\Doctrine\ORM\SQLParser\SQLSelectColumn;
use PHPZlc\PHPZlc\Doctrine\ORM\Untils\SQLHandle;
use PHPZlc\PHPZlc\Doctrine\ORM\Untils\Str;
use PHPZlc\Validate\Validate;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use PhpMyAdmin\SqlParser\Parser;
use PHPSQLParser\PHPSQLParser;

abstract class AbstractServiceRuleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, string $entityClass)
    {
        parent::__construct($registry, $entityClass);

        $this->telSqlArray['from'] = $this->getTableName();
        $this->telSqlArray['primaryKey'] = $this->getPrimaryKey();
        $this->telSqlArray['finalOrderBy'] = "sql_pre.{$this->getPrimaryKey()} DESC";

        if($this->getClassRuleMetadata()->hasRuleColumnOfColumnName('is_del')){
            $this->telSqlArray['falseDeleteField'] = 'is_del';
        }elseif($this->getClassRuleMetadata()->hasRuleColumnOfColumnName('isDel')){
            $this->telSqlArray['falseDeleteField'] = 'is_del';
        }

        $this->registerRules();
    }

    public $sqlArray = array(
        'alias' => '',
        'from' => '',
        'join' => '',
        'select' => '',
        'where' => '',
        'orderBy' => '',
        'finalOrderBy' => '',
        'primaryKey' => '',
        'aliasIncrease' => '',
        'falseDeleteField' => ''
    );

    public $telSqlArray = array(
        'alias' => 't',
        'from' => '',
        'join' => '',
        'select' => '',
        'where' => '',
        'orderBy' => '',
        'finalOrderBy' => '',
        'primaryKey' => '',
        'aliasIncrease' => 0,
        'falseDeleteField' => ''
    );

    /**
     * @var string
     */
    public $sql;

    /**
     * @var Rules
     */
    public $runRules;

    /**
     * @var ResultSetMappingBuilder
     */
    public $runResultSetMappingBuilder;

    /**
     * @var Rules
     */
    public $necessaryRules = null;

    /**
     * @var array
     */
    public $registerRules = [];

    /**
     * @var array
     */
    public $rewriteSqls = [];

##############################  表属性 start ##################################

    public function getTableName()
    {
        return $this->getClassMetadata()->getTableName();
    }

    public function getPrimaryKey()
    {
        return $this->getClassMetadata()->getColumnName($this->getClassMetadata()->getIdentifier()[0]);
    }

    public function setTableName()
    {
        return $this;
    }

#################################   规则 start ##################################

    /**
     * @param Rules|null $rules
     * @param ResultSetMappingBuilder|null $resultSetMappingBuilder
     * @param string $aliasChain sql_pre:a=>c,b=>a;at:a=>c,b=>a;
     */
    public function rules(Rules $rules = null, ResultSetMappingBuilder $resultSetMappingBuilder = null, $aliasChain = '')
    {
        if(empty($resultSetMappingBuilder)){
            $this->runResultSetMappingBuilder = new ResultSetMappingBuilder($this->getEntityManager());
        }else{
            $this->runResultSetMappingBuilder = clone $resultSetMappingBuilder;
        }

        if(empty($rules)){
            $this->runRules = new Rules();
        }else{
            $this->runRules = clone $rules;
        }

        $this->sql = '';
        $this->sqlArray = $this->telSqlArray;
        $this->runResultSetMappingBuilder->addRootEntityFromClassMetadata($this->getClassName(), $this->sqlArray['alias']);

        //系统规则
        if($this->runRules->issetRule(Rule::R_SELECT)){
            $this->sqlArray['select'] = $this->runRules->getRule(Rule::R_SELECT)->getValue();
        }else{
            $this->sqlArray['select'] = $this->getClassRuleMetadata()->getSelectSql([RuleColumn::PT_TABLE_IN, RuleColumn::PT_TYPE_TARGET]);
        }

        if($this->runRules->issetRule(Rule::R_JOIN)){
            $this->sqlArray['join'] =  $this->runRules->getRule(Rule::R_JOIN)->getValue();
        }

        if($this->runRules->issetRule(Rule::R_WHERE)){
            $this->sqlArray['where'] =  $this->runRules->getRule(Rule::R_WHERE)->getValue();
        }

        if($this->runRules->issetRule(Rule::R_ORDER_BY)){
            $this->sqlArray['orderBy'] =  $this->runRules->getRule(Rule::R_ORDER_BY)->getValue();
        }

        if(!empty($this->sqlArray['falseDeleteField'])){
            if(!$this->runRules->issetRule(Rule::R_FREED_FALSE_DEL)){
                $this->sqlArray['where'] .= " AND sql_pre.{$this->sqlArray['falseDeleteField']} = 0";
            }
        }


        //处理
        $this->process($this->runRules, $this->runResultSetMappingBuilder, $aliasChain);
    }

    /**
     * 规则注册
     *
     * @return mixed
     */
    abstract public function registerRules();

    /**
     * 注册覆盖规则
     *
     * @param $rule_suffix_name
     * @param $rule_description
     */
    final protected function registerCoverRule($rule_suffix_name, $rule_description = null)
    {
        $ruleColumn = $this->getClassRuleMetadata()->getRuleColumnOfRuleSuffixName($rule_suffix_name);

        if(empty($ruleColumn)) {
            $suffix_name = '';
            $ai_rule_name = '';
            foreach (Rule::getAllAIRule() as $aiRule) {
                if (strpos($rule_suffix_name, $aiRule) !== false) {
                    $suffix_name = rtrim($rule_suffix_name, $aiRule);
                    $ai_rule_name = $aiRule;
                    break;
                }
            }
            if(!empty($suffix_name)){
                $ruleColumn = $this->getClassRuleMetadata()->getRuleColumnOfRuleSuffixName($suffix_name);
                if(empty($ruleColumn)){
                    $this->registerRules[$rule_suffix_name] = $rule_description;
                }else {
                    $this->registerRules[$ruleColumn->propertyName . $ai_rule_name] = $rule_description;
                    $this->registerRules[$ruleColumn->name . $ai_rule_name] = $rule_description;
                }
            }else{
                $this->registerRules[$rule_suffix_name] = $rule_description;
            }
        }else{
            $this->registerRules[$ruleColumn->propertyName] = $rule_description;
            $this->registerRules[$ruleColumn->name] = $rule_description;
        }
    }

    /**
     * 注册必要规则
     *
     * @param Rule $rule
     */
    final protected function registerNecessaryRule(Rule $rule)
    {
        if(empty($this->necessaryRules)){
            $this->necessaryRules = new Rules();
        }

        $this->necessaryRules->addRule($rule);
    }

    final protected function registerRewriteSql($suffix_name, $sql)
    {
        $this->rewriteSqls[$suffix_name] = $sql;
    }

    /**
     * 规则重写
     *
     * @param Rule $currentRule
     * @param Rules $rules
     * @param ResultSetMappingBuilder $resultSetMappingBuilder
     * @return mixed
     */
    abstract public function ruleRewrite(Rule $currentRule, Rules $rules, ResultSetMappingBuilder $resultSetMappingBuilder);

    /**
     * 处理 识别分析在这的sql资源规则资源为SQL自动补充关联；
     */
    private function process(Rules $rules, ResultSetMappingBuilder $resultSetMappingBuilder, $aliasChain)
    {
        //>> 分析将需要自动引用的规则引入  方法；试最小资源满足目的运行  执行顺序 重写规则 > 表外字段规则 > JOIN缺失规则
        $sqlArray = $this->sqlArray;
        $cloneResultSetMappingBuilder = clone $resultSetMappingBuilder;
        //> 执行规则生成最基本的SQL 这时如果有需要调用的自动规则就以及加入到Rules中了；
        $this->rulesProcess($rules, $cloneResultSetMappingBuilder);
        //> SQL分析进行更智能的识别
        $sqlParser = new SQLParser($this->generateSql());
        //> 判断引用的表外字段依次的加入规则
        foreach ($sqlParser->getUseFieldsOFPreGrouping() as $pre => $fields){
            $classRuleMetadata = $this->classRuleMetadataOfPre($pre, $resultSetMappingBuilder);
            if(!empty($classRuleMetadata)){
                foreach ($fields as $field => $fieldParam){
                    $ruleColumn = $classRuleMetadata->getRuleColumnOfRuleSuffixName($fieldParam['column']);
                    if(!empty($ruleColumn)){
                        $rules->addRules($ruleColumn->rules);
                    }
                }
            }
        }

        //> 添加必须执行的规则
        foreach ($cloneResultSetMappingBuilder->aliasMap as $pre => $entity){
            $serviceRuleRepository = $this->getServiceRuleRepository($pre, $entity);
            if(!empty($serviceRuleRepository->necessaryRules)) {
                foreach ($serviceRuleRepository->necessaryRules->getRules() as $rule) {
                    if($serviceRuleRepository->sqlArray['alias'] != $this->sqlArray['alias']) {
                        $rule->editPre($serviceRuleRepository->sqlArray['alias']);
                    }
                    $rules->addRule($rule);
                }
            }
        }


        //> 判断是否存在可以连JOIN但确没有连的规则； 这是自动调用连接规则； [放弃这个设计；用处不大]

        //>>所有的规则执行好了则进入正式执行
        $this->sqlArray = $sqlArray;
        $rules->isNotAddRule = true;
        unset($cloneResultSetMappingBuilder);
        unset($sqlArray);

        $this->rulesProcess($rules, $resultSetMappingBuilder);

        //>> 整理SQL 如果主表主键没有查询则对象不会生成
        //> 识别 * 字段  将*替换成具体的字段 如果存在需要重新分析SQL
        $isSqlParsers = false;
        foreach ($sqlParser->selectColumnsOfColumn as $column => $SQLSelectColumn){
            if($SQLSelectColumn->name == '*'){
                if(empty($SQLSelectColumn->fieldPre)){
                    $SQLSelectColumn->fieldPre = 'sql_pre';
                }
                $classRuleMetadata = $this->classRuleMetadataOfPre($SQLSelectColumn->fieldPre, $resultSetMappingBuilder);
                if(!empty($classRuleMetadata)){
                    $this->sqlArray['select'] = str_replace($SQLSelectColumn->cloumn, $classRuleMetadata->getSelectSql([RuleColumn::PT_TYPE_TARGET, RuleColumn::PT_TABLE_IN], $SQLSelectColumn->fieldPre), $this->sqlArray['select']);
                    $isSqlParsers = true;
                }
            }else{
                if(empty($SQLSelectColumn->fieldPre)){
                    $this->sqlArray['select'] = str_replace($SQLSelectColumn->cloumn, 'sql_pre.' . $SQLSelectColumn->name,  $this->sqlArray['select']);
                    $isSqlParsers = true;
                }
            }
        }


        //移除字段规则
        if($rules->issetRule(Rule::R_HIDE_SELECT)){
            $hide_select = explode(',' , $rules->getRule(Rule::R_HIDE_SELECT)->getValue());
            if(empty($hide_select)){
                throw new PHPZlcException('R_HIDE_SELECT 规则不能为空');
            }else{
                foreach ($hide_select as $hide_value){
                    $hide_value = trim($hide_value);
                    if(!empty($hide_value)){
                        $hide_value_arr = explode('.' , $hide_value);
                        if(count($hide_value_arr) == 1){
                            $pre = 'sql_pre';
                            $hide = $hide_value_arr[0];
                        }else{
                            $pre = $hide_value_arr[0];
                            $hide = $hide_value_arr[1];
                        }
                        $classRuleMetadata = $this->classRuleMetadataOfPre($pre, $resultSetMappingBuilder);
                        if(!empty($classRuleMetadata)){
                            $ruleColumn = $classRuleMetadata->getRuleColumnOfRuleSuffixName($hide);
                            if(empty($ruleColumn)){
                                $this->sqlArray['select'] = str_replace($pre . '.' . $hide, '', $this->sqlArray['select']);
                            }else{
                                $this->sqlArray['select'] = str_replace($pre . '.' . $ruleColumn->name, '', $this->sqlArray['select']);
                                $this->sqlArray['select'] = str_replace($pre . '.' . $ruleColumn->propertyName, '', $this->sqlArray['select']);
                            }
                        }else{
                            $this->sqlArray['select'] = str_replace($pre . '.' . $hide, '', $this->sqlArray['select']);
                        }
                        //把出现的两个,的部分给移除
                        $this->sqlArray['select'] = preg_replace("/,[\S\s],/",",", $this->sqlArray['select']);
                    }
                }

                $this->sqlArray['select'] = rtrim(trim($this->sqlArray['select']), ',');

                if(empty($this->sqlArray['select'])){
                    throw new PHPZlcException('R_HIDE_SELECT 移除后 select 不可为空');
                }
            }

            if(!$isSqlParsers){
                $isSqlParsers = true;
            }
        }

        if($isSqlParsers){
            $sqlParser = new SQLParser($this->generateSql());
            unset($isSqlParsers);
        }

        //>> 整理SQL 如果主表主键没有查询则对象不会生成
        if(
            !isset($sqlParser->selectColumns[$this->getPrimaryKey()])
            &&
            !isset($sqlParser->selectColumns[$this->getClassMetadata()->getFieldName($this->getPrimaryKey())])
        ){
            $this->sqlArray['select'] = 'sql_pre.' . $this->getPrimaryKey() . ', ' . $this->sqlArray['select'];
            $resultSetMappingBuilder->addFieldResult($this->sqlArray['alias'], $this->getPrimaryKey(), $this->getClassMetadata()->getFieldName($this->getPrimaryKey()));
        }

        //>> 加入最终排序字段 最终排序字段不应该当是表外字段
        if(empty($this->sqlArray['orderBy'])){
            $this->sqlArray['orderBy'] = $this->sqlArray['finalOrderBy'];
        }else{
            $this->sqlArray['orderBy'] .= ',' . $this->sqlArray['finalOrderBy'];
        }

        //>> 查询字段绑定
        /**
         * @var SQLSelectColumn[] $SQLSelectColumns
         */
        foreach ($sqlParser->getSelectColumnFieldsOFPreGrouping() as $pre => $SQLSelectColumns){
            $classRuleMetadata = $this->classRuleMetadataOfPre($pre, $resultSetMappingBuilder);
            if(!empty($classRuleMetadata)){
                foreach ($SQLSelectColumns as $SQLSelectColumn){
                    $ruleColumn = $classRuleMetadata->getRuleColumnOfRuleSuffixName($SQLSelectColumn->fieldName);
                    if(!empty($ruleColumn)) {
                        if($ruleColumn->propertyType != RuleColumn::PT_TYPE_TARGET) {
                            $resultSetMappingBuilder->addFieldResult($SQLSelectColumn->fieldPre == 'sql_pre' ? $this->sqlArray['alias'] : $SQLSelectColumn->fieldPre, $SQLSelectColumn->name, $ruleColumn->propertyName);
                            if($ruleColumn->propertyType == RuleColumn::PT_TABLE_OUT && !$SQLSelectColumn->isAs){
                                $this->sqlArray['select'] = str_replace($SQLSelectColumn->cloumn, $SQLSelectColumn->cloumn .' as ' . $SQLSelectColumn->name, $this->sqlArray['select']);
                            }
                        }else{
                            $tar_pre = array_search($ruleColumn->targetEntity, $resultSetMappingBuilder->aliasMap);
                            if(empty($tar_pre)) {
                                $joinClassRuleMetadata = $this->getClassRuleMetadata($this->getEntityManager()->getClassMetadata($ruleColumn->targetEntity));
                                if ($joinClassRuleMetadata) {
                                    $tar_pre = $this->getAliasIncrease();
                                    $resultSetMappingBuilder->addJoinedEntityResult($ruleColumn->targetEntity, $tar_pre, $SQLSelectColumn->fieldPre == 'sql_pre' ? $this->sqlArray['alias'] : $SQLSelectColumn->fieldPre  , $ruleColumn->propertyName, array($ruleColumn->targetName => $ruleColumn->name));
                                }
                            }
                            if(!empty($tar_pre)) {
                                $resultSetMappingBuilder->addFieldResult($SQLSelectColumn->fieldPre == 'sql_pre' ? $tar_pre : $SQLSelectColumn->fieldPre, $SQLSelectColumn->name,  $this->getEntityManager()->getClassMetadata($ruleColumn->targetEntity)->getFieldName($ruleColumn->targetName));
                            }
                        }
                    }
                }
            }
        }

        //字段替换
        $aliasChainParser = $this->aliasChainParser($aliasChain);
        foreach ($sqlParser->getUseFieldsOFPreGrouping() as $pre => $fields){
            $classRuleMetadata = $this->classRuleMetadataOfPre($pre, $resultSetMappingBuilder);
            if(!empty($classRuleMetadata)){
                foreach ($fields as $field => $fieldParam){
                    $ruleColumn = $classRuleMetadata->getRuleColumnOfRuleSuffixName($fieldParam['column']);
                    if(!empty($ruleColumn)){
                        foreach ($this->sqlArray as $key => $value){
                            //如果表外字段在select中存在则直接使用select中的字段名;表外字段一般为子查询；直接取字段名可以避免重复子查询
                            if($key == 'orderBy' && isset($sqlParser->selectColumnsOfColumn[$field]) && $ruleColumn->propertyType == RuleColumn::PT_TABLE_OUT){
                                $this->sqlArray[$key] = str_replace($field, $sqlParser->selectColumnsOfColumn[$field]->name, $value);
                            }else{
                                if($ruleColumn->propertyType == RuleColumn::PT_TABLE_OUT && isset($aliasChainParser[$pre])){
                                    $alias = array_merge($aliasChainParser[$pre], ['sql_pre' => $pre]);
                                }else{
                                    $alias = ['sql_pre' => $pre];
                                }

                                if(array_key_exists($ruleColumn->name, $this->rewriteSqls)){
                                    $this->sqlArray[$key] = str_replace($field, SQLHandle::sqlProcess($this->rewriteSqls[$ruleColumn->name], $alias), $value);
                                }elseif(array_key_exists($ruleColumn->propertyName, $this->rewriteSqls)) {
                                    $this->sqlArray[$key] = str_replace($field, SQLHandle::sqlProcess($this->rewriteSqls[$ruleColumn->propertyName], $alias), $value);
                                }else{
                                    $this->sqlArray[$key] = str_replace($field, $ruleColumn->getSql($alias), $value);
                                }
                            }
                        }
                    }
                }
            }
        }

        if(!empty($this->sqlArray)){
            $this->sqlArray['orderBy'] = ' ORDER BY ' . $this->sqlArray['orderBy'];
        }
    }

    private function rulesProcess(Rules $rules, ResultSetMappingBuilder $resultSetMappingBuilder)
    {
        foreach ($rules->getRules() as $rule) {
            if (in_array($rule->getName(), Rule::$defRule)) {
                continue;
            }

            $classRuleMetadata = $this->classRuleMetadataOfPre($rule->getPre(), $resultSetMappingBuilder);

            if(empty($classRuleMetadata)){
                continue;
            }

            $ServiceRuleRepository = $this->getServiceRuleRepository($rule->getPre() == 'sql_pre' ? $this->sqlArray['alias'] : $rule->getPre(), $classRuleMetadata->getClassMetadata()->getName());

            if (array_key_exists($rule->getSuffixName(), $ServiceRuleRepository->registerRules)) {
                $ServiceRuleRepository->ruleRewrite($rule, $rules, $resultSetMappingBuilder);
            } else {
                $ruleColumn = $classRuleMetadata->getRuleColumnOfRuleSuffixName($rule->getSuffixName());

                if (!empty($ruleColumn)) {
                    //where从句
                    if(Validate::isRealEmpty($rule->getValue())){
                        $ServiceRuleRepository->sqlArray['where'] .= " AND ({$ruleColumn->getSqlComment($rule->getPre())} = '' OR {$ruleColumn->getSqlComment($rule->getPre())} is NULL)";
                    }else {
                        $ServiceRuleRepository->sqlArray['where'] .= " AND {$ruleColumn->getSqlComment($rule->getPre())} = '{$rule->getValue()}' ";
                    }
                } elseif (!Validate::isRealEmpty($rule->getValue())) {
                    if (strpos($rule->getName(), Rule::RA_CONTRAST) !== false) {
                        //where从句
                        $ruleColumn = $classRuleMetadata->getRuleColumnOfRuleSuffixName($rule->getSuffixName(), Rule::RA_CONTRAST);
                        if (!empty($ruleColumn)) {
                            $ServiceRuleRepository->sqlArray['where'] .= " AND {$ruleColumn->getSqlComment($rule->getPre())} {$rule->getValue()[0]} '{$rule->getValue()[1]}' ";
                        }
                    } elseif (strpos($rule->getName(), Rule::RA_IN) !== false) {
                        //where从句
                        $ruleColumn = $classRuleMetadata->getRuleColumnOfRuleSuffixName($rule->getSuffixName(), Rule::RA_IN);
                        if (!empty($ruleColumn)) {
                            $ServiceRuleRepository->sqlArray['where'] .= " AND {$ruleColumn->getSqlComment($rule->getPre())} in ({$rule->getValue()}) ";
                        }
                    } elseif (strpos($rule->getName(), Rule::RA_IS) !== false) {
                        //where从句
                        $ruleColumn = $classRuleMetadata->getRuleColumnOfRuleSuffixName($rule->getSuffixName(), Rule::RA_IS);
                        if (!empty($ruleColumn)) {
                            $ServiceRuleRepository->sqlArray['where'] .= " AND {$ruleColumn->getSqlComment($rule->getPre())} is {$rule->getValue()} ";
                        }
                    } elseif (strpos($rule->getName(), Rule::RA_LIKE) !== false) {
                        //where从句
                        $ruleColumn = $classRuleMetadata->getRuleColumnOfRuleSuffixName($rule->getSuffixName(), Rule::RA_LIKE);
                        if (!empty($ruleColumn)) {
                            $ServiceRuleRepository->sqlArray['where'] .= " AND {$ruleColumn->getSqlComment($rule->getPre())} LIKE '{$rule->getValue()}' ";
                        }
                    } elseif (strpos($rule->getName(), Rule::RA_ORDER_BY) !== false) {
                        //orderBy从句
                        $ruleColumn = $classRuleMetadata->getRuleColumnOfRuleSuffixName($rule->getSuffixName(), Rule::RA_ORDER_BY);
                        if (!empty($ruleColumn)) {
                            if (empty($ServiceRuleRepository->sqlArray['orderBy'])) {
                                $ServiceRuleRepository->sqlArray['orderBy'] = " {$ruleColumn->getSqlComment($rule->getPre())} {$rule->getValue()}";
                            } else {
                                $ServiceRuleRepository->sqlArray['orderBy'] .= ',' . " {$ruleColumn->getSqlComment($rule->getPre())} {$rule->getValue()}";
                            }
                        }
                    } elseif (strpos($rule->getName(), Rule::RA_JOIN) !== false) {
                        //JOIN从句
                        $ruleColumn = $classRuleMetadata->getRuleColumnOfRuleSuffixName($rule->getSuffixName(), Rule::RA_JOIN);
                        if (!empty($ruleColumn)) {
                            if($ruleColumn->isEntity){
                                $joinclassRuleMetadata = $this->getEntityManager()->getClassMetadata($ruleColumn->targetEntity);
                                $type = isset($rule->getValue()['type']) ? $rule->getValue()['type']: ' LEFT JOIN ';
                                $tableName = isset($rule->getValue()['tableName']) ? $rule->getValue()['tableName'] : $joinclassRuleMetadata->getTableName();
                                $alias = isset($rule->getValue()['alias']) ? $rule->getValue()['alias'] : die($rule->getName() . '缺少alias');
                                $on = isset($rule->getValue()['on']) ? $rule->getValue()['on'] : $ruleColumn->getSqlComment($rule->getPre()) . ' = ' . $rule->getValue()['alias'] . '.' . $ruleColumn->targetName;
                                $ServiceRuleRepository->sqlArray['join'] .= " {$type} {$tableName} AS {$alias} ON {$on} ";
                                if(!array_key_exists($alias, $resultSetMappingBuilder->aliasMap)){
                                    $resultSetMappingBuilder->addJoinedEntityFromClassMetadata($ruleColumn->targetEntity, $alias, $rule->getPre() == 'sql_pre' ? $this->sqlArray['alias'] : $rule->getPre(), $ruleColumn->propertyName, array($ruleColumn->targetName => $ruleColumn->name));
                                }
                            }
                        }
                    } elseif (strpos($rule->getName(), Rule::RA_SQL) !== false) {
                        //表外字段SQL重写
                        $ruleColumn = $classRuleMetadata->getRuleColumnOfRuleSuffixName($rule->getSuffixName(), Rule::RA_SQL);
                        if(!empty($ruleColumn)) {
                            $this->registerRewriteSql($ruleColumn->name, $rule->getValue());
                        }
                    } elseif (strpos($rule->getName(), Rule::RA_NOT_REAL_EMPTY) !== false) {
                        //WHERE从句 如果匹配值真为空则不生效
                        $ruleColumn = $classRuleMetadata->getRuleColumnOfRuleSuffixName($rule->getSuffixName(), Rule::RA_NOT_REAL_EMPTY);
                        if(!empty($ruleColumn)) {
                            $ServiceRuleRepository->sqlArray['where'] .= " AND {$ruleColumn->getSqlComment($rule->getPre())} = '{$rule->getValue()}' ";
                        }
                    }
                }
            }

            if($rule->getPre() !== 'sql_pre') {
                //将其他实体的结构拼接到主结构中
                if(!empty($ServiceRuleRepository->sqlArray['select'])) {
                    $this->sqlArray['select'] .= $ServiceRuleRepository->getSql($ServiceRuleRepository->sqlArray['select']);
                }
                if(!empty($ServiceRuleRepository->sqlArray['join'])) {
                    $this->sqlArray['join'] .= $ServiceRuleRepository->getSql($ServiceRuleRepository->sqlArray['join']);
                }
                if(!empty($ServiceRuleRepository->sqlArray['where'])) {
                    $this->sqlArray['where'] .= $ServiceRuleRepository->getSql($ServiceRuleRepository->sqlArray['where']);
                }

                if(!empty($ServiceRuleRepository->sqlArray['orderBy'])){
                    if(empty($this->sqlArray['orderBy'])){
                        $this->sqlArray['orderBy'] = $ServiceRuleRepository->getSql($ServiceRuleRepository->sqlArray['orderBy']);
                    }else{
                        $this->sqlArray['orderBy'] .= ',' . $ServiceRuleRepository->getSql($ServiceRuleRepository->sqlArray['orderBy']);
                    }
                }
            }
        }
    }

    /**
     * @param string $pre
     * @param ResultSetMappingBuilder $resultSetMappingBuilder
     * @return null|ClassRuleMetaData
     */
    private function classRuleMetadataOfPre($pre = 'sql_pre', ResultSetMappingBuilder $resultSetMappingBuilder)
    {
        if($pre == 'sql_pre'){
            return $this->getClassRuleMetadata();
        }else{
            if(array_key_exists($pre, $resultSetMappingBuilder->aliasMap)){
                return $this->getClassRuleMetadata($this->getEntityManager()->getClassMetadata($resultSetMappingBuilder->aliasMap[$pre]));
            }
        }

        return null;
    }

    public function getClassRuleMetadata(ClassMetadata $classMetadata = null)
    {
        if(empty($classMetadata)){
            $classMetadata = $this->getClassMetadata();
        }

        return ClassRuleMetaDataFactroy::getClassRuleMetadata($classMetadata);
    }

    /**
     * @param string $pre
     * @param null $entityName
     * @return AbstractServiceRuleRepository
     */
    private function getServiceRuleRepository($pre = 'sql_pre', $entityName = null)
    {
        if ($pre != $this->sqlArray['alias']) {
            $ServiceRuleRepository = $this->getEntityManager()->getRepository($entityName);
            $ServiceRuleRepository->sqlArray = $ServiceRuleRepository->telSqlArray;
            $ServiceRuleRepository->sqlArray['alias'] = $pre;
        } else {
            $ServiceRuleRepository = $this;
        }

        return $ServiceRuleRepository;
    }

#################################   工具 start ##################################

    private function generateSql()
    {
        return "SELECT {$this->sqlArray['select']} FROM {$this->sqlArray['from']} {$this->sqlArray['alias']} {$this->sqlArray['join']} WHERE 1 {$this->sqlArray['where']} {$this->sqlArray['orderBy']}";
    }

    private function getAliasIncrease()
    {
        $aliasIncrease = $this->sqlArray['alias'] . $this->sqlArray['aliasIncrease'];
        $this->sqlArray['aliasIncrease'] ++;
        return $aliasIncrease;
    }

    public function getSql($sql = null)
    {
        if(empty($sql)){
            $sql = $this->generateSql();
        }

        $this->sql = str_replace('sql_pre', $this->sqlArray['alias'], $sql);

        return $this->sql;
    }

    private function aliasChainParser($aliasChain)
    {
        $aliasChainParser = [];

        try {
            if(!empty($aliasChain)) {
                $a1 = explode(';', $aliasChain);
                foreach ($a1 as $v1) {
                    $a2 = explode(':', $v1);
                    $a3 = explode(',', $a2[1]);
                    foreach ($a3 as $v3) {
                        $a4 = explode('=>', $v3);
                        $aliasChainParser[$a2[0]][$a4[0]] = $a4[1];
                    }
                }
            }
        }catch (\Exception $exception){
            throw new PHPZlcException('aliasChain格式错误'. $exception->getMessage() .'；格式范例sql_pre:a=>c,b=>a;at:a=>c,b=>a');
        }

        return $aliasChainParser;
    }


#################################   工具 Result Serialization ##################################

    final public function arraySerialization($result, $decoratorMethodName = 'toArray', $decoratorMethodParams = []) : array
    {
        if(empty($result)){
            return [];
        }

        if(is_object($result)){
            return $this->$decoratorMethodName($result, $decoratorMethodParams);
        }else{
            $res = [];

            foreach ($result as $key => $value){
                $res[$key] = $this->$decoratorMethodName($value, $decoratorMethodParams);
            }

            return $res;
        }
    }

    final public function toArray($entity): array
    {
        $data = [];

        foreach ($this->getClassRuleMetadata()->getAllRuleColumn() as $ruleColumn){
            $methodName = 'get'.Str::asCamelCase($ruleColumn->propertyName);
            $methodReturn = $entity->$methodName();
            if(is_object($methodReturn)){
                try {
                    $data[$ruleColumn->propertyName] = $this->getEntityManager()->getRepository(get_class($methodReturn))->toArray($methodReturn);
                }catch (\Exception $exception){
                    $data[$ruleColumn->name] = $methodReturn->toArray();
                }
            }else{
                $returnValue = $entity->$methodName();

                switch ($ruleColumn->type){
                    case 'simple_array':
                    case 'json_array':
                        if(empty($returnValue)){
                            $returnValue = [];
                        }
                        break;
                    case 'boolean':
                        $returnValue = $returnValue ? 1 : 0;
                        break;
                    default:
                        if(Validate::isRealEmpty($returnValue)){
                            $returnValue = '';
                        }
                }

                $data[$ruleColumn->name] = $returnValue;
            }
        }

        return $data;
    }

    /**
     * 规则匹配
     *
     * @param Rule $currentRule
     * @param $rule_suffix_name
     * @return bool
     */
    final protected function ruleMatch(Rule $currentRule, $rule_suffix_name) : bool
    {
        if($currentRule->getSuffixName() == $rule_suffix_name){
            return true;
        }

        $ruleColumn = $this->getClassRuleMetadata()->getRuleColumnOfRuleSuffixName($currentRule->getSuffixName());

        if(!empty($ruleColumn)){
            if($ruleColumn->name == $rule_suffix_name ||  $ruleColumn->propertyName == $rule_suffix_name){
                return true;
            }
        }

        $suffix_name = '';
        $ai_rule_name = '';
        foreach (Rule::getAllAIRule() as $aiRule) {
            if (strpos($currentRule->getSuffixName(), $aiRule) !== false) {
                $suffix_name = rtrim($currentRule->getSuffixName(), $aiRule);
                $ai_rule_name = $aiRule;
                break;
            }
        }
        if(!empty($suffix_name)){
            $ruleColumn = $this->getClassRuleMetadata()->getRuleColumnOfRuleSuffixName($suffix_name);
            if(!empty($ruleColumn)) {
                if ($ruleColumn->name . $ai_rule_name == $rule_suffix_name || $ruleColumn->propertyName . $ai_rule_name == $rule_suffix_name) {
                    return true;
                }
            }
        }

        return false;
    }
}