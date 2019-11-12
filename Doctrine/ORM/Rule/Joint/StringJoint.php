<?php
/**
 * PhpStorm.
 * User: Jay
 * Date: 2018/11/22
 */

namespace PHPZlc\Kernel\Doctrine\ORM\Rule\Joint;

use PHPZlc\Kernel\Doctrine\ORM\Rule\InterfaceJoint;
use PHPZlc\Kernel\Doctrine\ORM\Rule\Rule;

class StringJoint implements InterfaceJoint
{
    function joint($current_rule_value, $upper_rule_value, $jointSort)
    {
        if($jointSort == Rule::DESC){
            $var = $current_rule_value;
            $current_rule_value = $upper_rule_value;
            $upper_rule_value = $var;
        }

        return $upper_rule_value . ' '.$current_rule_value;
    }

}