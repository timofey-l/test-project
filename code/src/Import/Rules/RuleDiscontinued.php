<?php

namespace App\Import\Rules;

use App\Import\Exceptions\DiscontinuedRuleException;

class RuleDiscontinued implements ImportRule
{
    private $fieldName;
    private $trueValue;

    /**
     * RuleDiscontinued constructor.
     * @param $fieldName
     * @param $trueValue
     */
    public function __construct($fieldName, $trueValue = 'yes')
    {

        $this->fieldName = $fieldName;
        $this->trueValue = $trueValue;
    }


    /**
     * Applies the rule to an item
     * @param $item
     * @return void
     * @throws DiscontinuedRuleException
     */
    public function apply(&$item)
    {
        if (isset($item[$this->fieldName]) && $item[$this->fieldName] === $this->trueValue) {
            throw new DiscontinuedRuleException($this);
        }
    }
}
