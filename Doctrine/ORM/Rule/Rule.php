<?php
namespace PHPZlc\PHPZlc\Doctrine\ORM\Rule;

use PHPZlc\PHPZlc\Abnormal\PHPZlcException;
use PHPZlc\PHPZlc\Doctrine\ORM\Rule\Joint\StringJoint;

class Rule
{
    /**
     * 默认规则
     *
     * @var array
     */
    public static $defRule = [
        self::R_SELECT, self::R_JOIN, self::R_WHERE, self::R_ORDER_BY, self::R_HIDE_SELECT, self::R_FREED_FALSE_DEL
    ];

    /**
     * 碰撞规则 - 取代
     */
    const REPLACE = 'replace';

    /**
     * 碰撞规则 - 放弃
     */
    const FORGO = 'forgo';

    /**
     * 碰撞规则 - 联合
     */
    const JOINT = 'joint';

    /**
     * 碰撞规则-联合-次序规则-正序  （上一级排在前面）
     */
    const ASC = 'asc';

    /**
     * 碰撞规则-联合-次序规则-倒序   （当前级排在前面）
     */
    const DESC = 'desc';

    /**
     * Repository 默认规则-select 指定查询内容
     */
    const R_SELECT = 'select';

    /**
     * Repository 默认规则-select 隐藏查询内容
     */
    const R_HIDE_SELECT = 'hide_select';

    /**
     * Repository 默认规则-where 释放假删除数据
     */
    const R_FREED_FALSE_DEL = 'freed_false_del';

    /**
     * Repository 默认规则-order_by  最高优先级排序
     */
    const R_ORDER_BY = 'order_by';  //默认支持规则order_by  自定义优先排序方式 （支持一维数组和字符串） 单个数组中为字符串  条件会自动拼接到sql中 参数开头必须是 ASC DESC

    /**
     * Repository 默认规则-where
     */
    const R_WHERE = 'where'; //默认支持规则where 自定义查询条件 数组（支持一维数组和字符串） 单个数组中为字符串  条件会自动拼接到sql中 参数开头必须是 AND 或者 OR

    /**
     * Repository 默认规则-join
     */
    const R_JOIN = 'join';

    /**
     * Repository 智能规则 模糊 用于所有字段的模糊查询
     */
    const RA_LIKE = '_like';

    /**
     * Repository 智能规则 排序  用户所有字段排序
     */
    const RA_ORDER_BY = '_order_by';

    /**
     * Repository 智能规则 比较  用于所有字段的条件  ['>', '12']  规则内容第一项为运算符 第二项为比较内容
     */
    const RA_CONTRAST = '_contrast';

    /**
     *  Repository 智能规则 用于连表
     */
    const RA_JOIN = '_join';

    /**
     * Repository 智能规则 用于重写字段的SQL表单式
     */
    const RA_SELECT = '_select';

    /**
     * Repository 智能规则 is 用于所有字段的条件
     */
    const RA_IS = '_is';

    /**
     * Repository 智能规则 in 用于所有字段的查询
     */
    const RA_IN = '_in';

    /**
     * Repository 智能规则  用于表外字段的sql重写
     */
    const RA_SQL = '_sql';

    /**
     * Repository 智能规则  用于筛选精确匹配 如果匹配值真为空则不生效
     */
    const RA_NOT_REAL_EMPTY = '_not_real_empty';


    /**
     * 规则名称
     *
     * @var string
     */
    private $name;


    /**
     * @var string
     */
    private $suffixName;

    /**
     * 规则前缀
     *
     * @var string
     */
    private $pre;

    /**
     * 规则内容
     *
     * @var void
     */
    private $value;

    /**
     * 碰撞规则
     *
     * @var string
     */
    private $collision;

    /**
     * @var JointInterface
     */
    private $jointClass;

    /**
     * @var 碰撞规则-联合-次序规则
     */
    private $jointSort;

    //Related

    /**
     * Rule constructor.
     * @param $name
     * @param $value
     * @param null $collision
     * @param null | InterfaceJoint $jointClass
     * @param $jointSort
     */
    public function __construct($name, $value ,$collision = null, $jointClass = null, $jointSort = null)
    {
        if(empty($name)){
            throw new PHPZlcException('Rule name 不能为空');
        }

        if($collision == null) {
            if(in_array($name, self::$defRule)) {
                $collision = self::JOINT;
            }else{
                $collision = self::REPLACE;
            }
        }

        if($collision == self::JOINT && empty($jointClass)){
            $jointClass = new StringJoint();
        }

        if($collision == self::JOINT && empty($jointSort)){
            $jointSort = self::ASC;
        }

        if(in_array($name, self::$defRule)){
            $this->name = $name;
        }else{
            $nameArr = explode('.', $name);
            $nameArrCount = count($nameArr);

            if($nameArrCount == 1){
                $this->pre = 'sql_pre';
                $this->suffixName = $nameArr[0];
            }elseif($nameArrCount == 2){
                $this->pre = $nameArr[0];
                $this->suffixName = $nameArr[1];
            }else {
                throw new PHPZlcException('Rule ' . $this->name . ' 长度不合法');
            }

            $this->name = $this->pre . '.' . $this->suffixName;
        }

        $this->value = $value;
        $this->collision = $collision;
        $this->jointClass = $jointClass;
        $this->jointSort = $jointSort;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return void|string|array|object
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function getPre()
    {
        return $this->pre;
    }

    public function getSuffixName()
    {
        return $this->suffixName;
    }

    /**
     * @return string
     */
    public function getCollision()
    {
        return $this->collision;
    }

    /**
     * @return JointInterface
     */
    public function getJointClass()
    {
        return $this->jointClass;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getJointSort()
    {
        return $this->jointSort;
    }

    public function editPre($pre)
    {
        if(!empty($this->pre)){
            $this->pre = $pre;
        }

        $this->name = $this->pre . '.' . $this->suffixName;
    }

    public static function getAllAIRule() : array
    {
        return [self::RA_IN, self::RA_JOIN, self::RA_ORDER_BY, self::RA_LIKE, self::RA_IS, self::RA_SELECT, self::RA_SQL, self::RA_CONTRAST, self::RA_NOT_REAL_EMPTY];
    }
}