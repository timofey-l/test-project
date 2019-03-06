<?php

namespace App\Import\Rules;

use App\Import\Exceptions\ImportRuleException;
use App\Import\Exceptions\ValidationRuleException;

class RuleNumber implements ImportRule
{
    /**
     * @var array
     */
    private $fields;


    /**
     * RuleValidate constructor.
     * @param array $fields
     */
    public function __construct($fields)
    {

        $this->fields = $fields;
    }

    /**
     * Applies the rule to an item
     * @param $item
     * @return void
     * @throws ImportRuleException
     */
    public function apply(&$item)
    {
        foreach ($this->fields as $field => $validationErrorMessage) {
            if (!isset($item[$field])) {
                throw new ImportRuleException($this);
            }

            if (($newValue = $this->process($item[$field])) === false) {
                throw new ValidationRuleException($this, $validationErrorMessage);
            } else {
                $item[$field] = $newValue;
            }
        }
    }

    /**
     * Processes the value and returns the value
     * @param $value
     * @return int|boolean
     */
    private function process($value)
    {

        $matchesRegex = preg_match('/^\d+\.?\d*$/', $value);
        if (!$matchesRegex) {
            return false;
        }

        if (strpos($value, '.') === false) {
            $result = (int) $value;
        } else {
            $result = (float) $value;
        }
        return $result;
    }
}
