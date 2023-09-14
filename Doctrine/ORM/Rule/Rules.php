<?php

namespace PHPZlc\PHPZlc\Doctrine\ORM\Rule;

class Rules
{
    private $rules = [];

    /**
     * @var bool 是否无法在添加新的规则;该参数较为特殊；
     */
    public $isNotAddRule = false;

    /**
     * Rules constructor.
     * @param Rules|null|array $rules
     */
    public function __construct($rules = null)
    {
        if(!empty($rules)) {
            if(is_array($rules)){
                $this->addRules($rules);
            }else{
                $this->rules = $rules->getRules();
            }
        }
    }

    /**
     * @param Rules|array $rules
     * @return $this|Rules
     */
    public function addRules($rules)
    {
        if(!empty($rules)) {
            if(is_array($rules)){
                return $this->addArrayRule($rules);
            }

            /**
             * @var Rule $rule
             */
            foreach ($rules->getRules() as $rule) {
                $this->addRule($rule);
            }
        }

        return $this;
    }

    private function addArrayRule(array $array_rule)
    {
        foreach ($array_rule as $rule => $value){
            if($value instanceof Rule){
                $this->addRule($value);
            }else{
                $this->addRule(new Rule($rule, $value));
            }
        }

        return $this;
    }

    public function addRule(Rule $rule)
    {
        if($this->isNotAddRule){
            return $this;
        }

        if($this->issetRule($rule->getName())) {
            switch ($this->getRule($rule->getName())->getCollision()){
                case Rule::REPLACE:
                    $rule->setValue($this->getRule($rule->getName())->getValue());
                    break;
                case Rule::JOINT:
                    $rule->setValue($this->getRule($rule->getName())->getJointClass()->joint($rule->getValue(), $this->getRule($rule->getName())->getValue(), $this->getRule($rule->getName())->getJointSort()));
                    break;
            }
        }

        $this->rules[$rule->getName()] = $rule;

        return $this;
    }

    public function toArray()
    {
        $rules = [];

        /**
         * @var Rule $rule
         */
        foreach ($this->rules as $rule) {
            $rules[$rule->getName()] = $rule->getValue();
        }

        return $rules;
    }

    /**
     * @return Rule[]
     */
    public function getRules()
    {
        return $this->rules;
    }

    public function getJoinRules()
    {
        $rules = [];

        foreach ($this->rules as $rule){
            if(strpos($rule->getName(), Rule::RA_JOIN) !== false) {
                $rules[] = $rule;
            }
        }

        return $rules;
    }

    public function getNotJoinRules()
    {
        $rules = [];

        foreach ($this->rules as $rule){
            if(strpos($rule->getName(), Rule::RA_JOIN) === false) {
                $rules[] = $rule;
            }
        }

        return $rules;
    }

    public function issetRule($rule_name)
    {
        return array_key_exists($rule_name, $this->getRules());
    }

    /**
     * @param $rule_name
     * @return Rule
     */
    public function getRule($rule_name)
    {
        return $this->rules[$rule_name];
    }

    /**
     * @param $rule_name
     */
    public function removeRule($rule_name)
    {
        unset($this->rules[$rule_name]);
    }

    /**
     * @param Rules|array|null $rules
     * @return Rules
     */
    public static function getAIRules($rules = null): Rules
    {
        if(empty($rules)){
            $rules = new Rules();
        }elseif(is_array($rules)){
            $rules = new Rules($rules);
        }

        return $rules;
    }
}