<?php

namespace App\Import\Rules;

use App\Import\Exceptions\ValidationRuleException;

class RuleValidate implements ImportRule {


    /**
     * RuleValidate constructor.
     */
    public function __construct()
    {

    }

    /**
     * Applies the rule to an item
     * @param $item
     * @return void
     * @throws ValidationRuleException
     */
    public function apply(&$item)
    {
        // check if all values present and not null
        foreach ($item as $key => $value) {
            if (is_null($value)) {
                throw new ValidationRuleException($this, "Value for key:$key not defined");
            }
        }
    }
}
