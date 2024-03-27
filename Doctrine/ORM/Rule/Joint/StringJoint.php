<?php
/**
 * PhpStorm.
 * User: Jay
 * Date: 2018/11/22
 */

namespace PHPZlc\PHPZlc\Doctrine\ORM\Rule\Joint;

use PHPZlc\PHPZlc\Doctrine\ORM\Rule\InterfaceJoint;
use PHPZlc\PHPZlc\Doctrine\ORM\Rule\Rule;

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