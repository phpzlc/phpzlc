<?php

namespace PHPZlc\Kernel\Doctrine\ORM\Rule;

interface InterfaceJoint
{
    /**
     *
     * @param mixed $current_rule_value  当前规则
     * @param mixed $upper_rule_value  上层规则
     * @param mixed $jointSort  碰撞规则-联合-次序规则 asc | desc
     * @return mixed
     */
    function joint($current_rule_value, $upper_rule_value, $jointSort);
}